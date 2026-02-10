<script>
import {notify, whatError} from "@/utils.js";
import config from "@/config.json";
import axios from "axios";

export default {
    name: "AddressComponent",
    data () {
        return {
            address: {
                address: "", door: "", doorphone: "",
                floor: "", flat: "", comment: "",
            },
            coords: [],
            variables: [],
            selectedAddress: '',
        }
    },
    async mounted () {
        this.loadAddress();
    },
    props: {
        userSelected: {
            type: Boolean,
            default: false,
        }
    },
    methods: {
        getString () {
            return this.address.address
                + (this.address.door.length > 0 ? ', ' + this.address.door + ' подъезд' : '')
                + (this.address.doorphone.length > 0 ? ', ' + this.address.doorphone + ' домофон' : '')
                + (this.address.floor.length > 0 ? ', ' + this.address.floor + ' этаж' : '')
                + (this.address.flat.length > 0 ? ', ' + this.address.flat + ' квартира' : '');
        },
        getComment () {
            return this.address.comment;
        },
        async getCoords () {
            console.log(this.address.address);
            console.log(this.user.addresses[this.user.address].address);

            if (this.coords.length > 0 && this.address.address === this.user.addresses[this.user.address].address) return this.coords;
            if (!window.ymaps3?.ready) notify('Ошибка Yandex Maps', 1);

            this.coords = [];

            const apiKey = '72462881-8725-458b-ab67-3676c9c9ca7b';
            const adr = "Самара. " + this.address.address;
            const url = `https://geocode-maps.yandex.ru/1.x/?apikey=${apiKey}&geocode=${adr}&format=json`;

            try {
                const resp = await fetch(url);
                const data = await resp.json();
                const feature = data.response.GeoObjectCollection.featureMember[0]?.GeoObject;
                if (!feature) {
                    notify('Ошибка Yandex Maps', 1);
                    return this.coords;
                }

                const components = feature.metaDataProperty.GeocoderMetaData.Address.Components;
                const area = components.find(c => c.kind === "area")?.name;
                if (area !== 'городской округ Самара') {
                    notify("Доставка только по Самаре!", 1)
                    return this.coords;
                }

                let city, street, house;
                for (const c of components) {
                    if (c.kind === "locality") city = c.name;
                    if (c.kind === "street") street = c.name;
                    if (c.kind === "house") house = c.name;
                }
                if (!city || !street || !house) {
                    notify('Неправильный адрес', 1);
                    this.address.address = "";
                    return this.coords;
                }
                else this.address.address = city + ", " + street + ", " + house;

                const pos = feature.Point.pos;
                const [lonStr, latStr] = pos.split(" ");
                this.coords = [parseFloat(latStr), parseFloat(lonStr)];
            } catch (e) {
                console.log(e);
            }
            return this.coords;
        },
        loadAddress () {
            if (!this.user.id) return;
            if (this.userSelected && this.user.address != null && this.user.addresses[this.user.address]) {
                this.address = {...this.user.addresses[this.user.address]};
                this.address.comment = this.user.addresses[this.user.address].commentAddress;

                this.coords = [this.user.addresses[this.user.address].latitude, this.user.addresses[this.user.address].longitude];
            }
        },
        async checkAddress (latitude, longitude) {
            if (latitude == null || longitude == null) {
                notify("Не выбрана точка", 1);
                return console.log("Undefined latitude or longitude")
            }

            const apiKey = '72462881-8725-458b-ab67-3676c9c9ca7b';
            const url = `https://geocode-maps.yandex.ru/1.x/?apikey=${apiKey}&geocode=${latitude},${longitude}&format=json`;
            try {
                const resp = await fetch(url);
                const data = await resp.json();
                const feature = data.response.GeoObjectCollection.featureMember[0]?.GeoObject;
                if (!feature) return notify("Не выбрана точка", 1);

                const components = feature.metaDataProperty.GeocoderMetaData.Address.Components;
                const area = components.find(c => c.kind === "area")?.name;
                if (area !== 'городской округ Самара') {
                    this.address.address = "";
                    return notify("Доставка только по Самаре!", 1)
                }
            } catch (e) {}

            this.$emit('updateMarker');

            let rules = [
                ["address", this.address.address.length > 5],
                ["door", /^(?:0|[1-9][0-9]*)$/.test(this.address.door)],
                ["doorphone", /^(?:0|[1-9][0-9]*)$/.test(this.address.doorphone)],
                ["floor", /^(?:0|[1-9][0-9]*)$/.test(this.address.floor)],
                ["flat", /^(?:0|[1-9][0-9]*)$/.test(this.address.flat)],
            ]

            let isError = false;
            for (let rule of rules) {
                let el = document.querySelector("#" + rule[0]).parentNode;
                el.style.border = "";
                el.querySelector('label').style.color = "";
            }

            for (let rule of rules) {
                if (!rule[1]) {
                    isError = true;

                    let el = document.querySelector("#" + rule[0]).parentNode;
                    el.style.border = "1px solid #f44336";
                    el.querySelector('label').style.color = "#f44336";
                }
            }
            if (isError) notify("Неправильный адрес!", 1);
            else {
                let newUser = {...this.user};
                if (newUser.addresses == null) newUser.addresses = [];

                newUser.addresses.push({
                    address: this.address.address,
                    door: this.address.door,
                    doorphone: this.address.doorphone,
                    floor: this.address.floor,
                    flat: this.address.flat,
                    latitude: latitude,
                    longitude: longitude,
                    commentAddress: this.address.comment ?? '',
                });
                newUser.address = Object.keys(newUser.addresses).length-1;

                this.$store.dispatch("updateUser", newUser);
                notify("Успешно сохранено!", 0);
                this.$emit('close');

                await axios.post(config.backend + "auth/update", {
                    addresses: Object.keys(newUser.addresses).length === 0 ? null : newUser.addresses,
                    address: Object.keys(newUser.addresses).length - 1,
                    initData: window.Telegram.WebApp.initData,
                }).then((response) => {

                }).catch((error) => {
                    notify(whatError(error),1);
                })
            }
        },
        async oninp (event) {
            let input = event.target;
            const value = input.value.trim();
            if (value.length < 3) return this.variables = [];

            const query = 'Самара, ' + value;
            if (window.ymaps3?.suggest) {
                try {
                    const result = await ymaps3.suggest({ text: query, results: 5 });
                    console.log(result);
                    this.variables = result.filter(a => a.value.includes('Самара'));
                    console.log(this.variables);
                } catch (e) {
                    // this.variables = [];
                }
                requestAnimationFrame(() => this.$refs.addressSelect.focus())
            }
        }
    },
    computed: {
        user () {
            return this.$store.state.user;
        }
    },
    watch: {
        user () {
            this.loadAddress();
        },
        selectedAddress () {
            // this.variables = [];
            // this.address.address = this.selectedAddress;
            // this.selectedAddress = '';
        }
    }
}
</script>

<template>
    <div class="addressComponent">
        <div>
            <div>
                <label for="">Город, улица и дом</label>
                <input type="text" id="address" @input="oninp" v-model="address.address" placeholder="Адрес">
                {{variables.length}}
                <select ref="addressSelect" v-model="selectedAddress" style="z-index: -1; opacity: 0" v-if="variables.length" name="" id="">
                    <option v-for="v in variables" :value="v.value">{{v.value}}</option>
                </select>
            </div>
        </div>
        <div>
            <div>
                <label for="">Подъезд</label>
                <input type="text" id="door" v-model="address.door" placeholder="Подъезд">
            </div>
            <div>
                <label for="">Домофон</label>
                <input type="text" id="doorphone" v-model="address.doorphone" placeholder="Домофон">
            </div>
        </div>
        <div>
            <div>
                <label for="">Этаж</label>
                <input type="text" id="floor" v-model="address.floor" placeholder="Этаж">
            </div>
            <div>
                <label for="">Квартира</label>
                <input type="text" id="flat" v-model="address.flat" placeholder="Квартира">
            </div>
        </div>
        <div>
            <div class="addressComponent_without">
                <input type="text" id="comment" v-model="address.comment" placeholder="Комментарий для курьера">
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
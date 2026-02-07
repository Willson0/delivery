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
        getCoords () {
            if (this.coords.length > 0) return this.coords;
            if (!window.ymaps3?.ready) notify('Ошибка Yandex Maps', 1);

            ymaps3.search({
                'text': "Самара. " + this.address.address,
            }).then((res) => {
                this.coords = res[0].geometry.coordinates;
            });
            return this.coords;
        },
        loadAddress () {
            if (this.userSelected && this.user.address && this.user.addresses[this.user.address]) {
                this.address = this.user.addresses[this.user.address];
                this.address.comment = this.user.addresses[this.user.address].commentAddress;

                this.coords = [this.user.address.latitude, this.user.address.longitude];
            }
        },
        async checkAddress (latitude, longitude) {
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
                    commentAddress: this.address.comment,
                });

                this.$store.dispatch("updateUser", newUser);
                notify("Успешно сохранено!", 0);

                await axios.post(config.backend + "auth/update", {
                    cart: Object.keys(newUser.addresses).length === 0 ? null : newUser.addresses,
                    initData: window.Telegram.WebApp.initData,
                }).then((response) => {

                }).catch((error) => {
                    notify(whatError(error),1);
                })
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
        }
    }
}
</script>

<template>
    <div class="addressComponent">
        <div>
            <div>
                <label for="">Город, улица и дом</label>
                <input type="text" id="address" v-model="address.address" placeholder="Адрес">
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
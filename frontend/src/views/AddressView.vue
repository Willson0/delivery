<script>
import {closeOverlay, notify, openOverlay, whatError} from "@/utils.js";
import AddressComponent from "@/components/AddressComponent.vue";
import axios from "axios";
import config from "@/config.json";

export default {
    name: "AddressView",
    components: {AddressComponent},
    data () {
        return {
            address: {},
            selected: 0,
            coords: [],
        }
    },
    async mounted () {
        const waitYmapsReady = async () => {
            if (window.ymaps3?.ready) {
                await window.ymaps3.ready;
                this.initMap();
            } else {
                setTimeout(waitYmapsReady, 100);
            }
        };
        waitYmapsReady();
    },
    methods: {
        closeOverlay,
        openOverlay (overlay) {
            window.Telegram.WebApp.BackButton.offClick(window.backByQueryFunction);
            window.Telegram.WebApp.BackButton.onClick(this.closeMapOverlay);
            window.Telegram.WebApp.BackButton.show();

            openOverlay (overlay);
        },
        closeMapOverlay () {
            closeOverlay('address_adding_overlay');

            window.Telegram.WebApp.BackButton.offClick(this.closeMapOverlay);
            window.Telegram.WebApp.BackButton.onClick(window.backByQueryFunction);
            window.Telegram.WebApp.BackButton.show();
        },
        async initMap() {
            const mapInputSearch = document.querySelector('.addressComponent>div>div>input');
            const {YMap, YMapDefaultSchemeLayer, YMapListener, YMapMarker, YMapDefaultFeaturesLayer} = window.ymaps3;

            const map = new YMap(
                this.$refs.map,
                {
                    location: {
                        center: [37.588144, 55.733842],
                        zoom: 15
                    }
                }
            );

            map.addChild(new YMapDefaultSchemeLayer());
            map.addChild(new YMapDefaultFeaturesLayer());

            const icon = document.createElement('img');
            icon.className = 'marker';
            icon.style.width = "24px";
            icon.style.height = "24px";
            icon.style.objectFit = "cover";
            icon.style.transform = "translate(-50%, -50%)"
            icon.src = "/pizza.png"

            const marker = new YMapMarker({
                coordinates: [37.588144, 55.733842]
            },icon);
            map.addChild(marker);
            const click = (object,event) => {
                marker.update({
                    coordinates: event.coordinates
                });
                this.geocodeYandex(
                    event.coordinates[0].toFixed(6),
                    event.coordinates[1].toFixed(6),
                );
            };
            const mapListener = new YMapListener({
                layer: 'any',
                onFastClick: click,
                onClick: click,
            });
            map.addChild(mapListener);

            mapInputSearch.addEventListener("keydown", (event) => {
                if(event.keyCode === 13){
                    ymaps3.search({
                        'text': mapInputSearch.value
                    }).then((res) => {
                        let center_update = res[0].geometry.coordinates;
                        map.update({
                            location: {
                                center: center_update,
                                zoom: 15,
                                duration: 400
                            }
                        });

                        marker.update({
                            coordinates:center_update
                        });

                        console.log(center_update);
                        this.coords = center_update;
                        this.geocodeYandex(center_update[0], center_update[1])
                    })
                }
            });
        },
        async geocodeYandex(lng, lat) {
            const apiKey = '72462881-8725-458b-ab67-3676c9c9ca7b';
            const url = `https://geocode-maps.yandex.ru/1.x/?apikey=${apiKey}&geocode=${lng},${lat}&format=json`;
            const mapInputSearch = document.querySelector('.addressComponent>div>div>input');

            try {
                const resp = await fetch(url);
                const data = await resp.json();
                const feature = data.response.GeoObjectCollection.featureMember[0]?.GeoObject;
                if (!feature) return null;

                const components = feature.metaDataProperty.GeocoderMetaData.Address.Components;
                let city, street, house;
                for (const c of components) {
                    if (c.kind === "locality") city = c.name;
                    if (c.kind === "street") street = c.name;
                    if (c.kind === "house") house = c.name;
                }
                if (!city || !street || !house) mapInputSearch.value = "";
                else mapInputSearch.value = city + ", " + street + ", " + house;
            } catch (e) {
                mapInputSearch.value = "";
            }
        },
        async save () {
            this.$refs.addressComponent.checkAddress(this.coords[0], this.coords[1]);
        },
        async changeAddress (key) {
            let newUser = {...this.user};

            newUser.address = key;
            this.$store.dispatch("updateUser", newUser);

            await axios.post(config.backend + "auth/update", {
                address: key,
                initData: window.Telegram.WebApp.initData,
            }).then((response) => {})
            .catch((error) => {
                notify(whatError(error),1);
            })
        }
    },
    computed: {
        user () {
            return this.$store.state.user;
        }
    }
}
</script>

<template>
    <div class="address">
        <div v-for="(address, key) in user.addresses" @click="changeAddress(key)" :class="{active: user.address === key}">
            <div class="address_checkbox"></div>
            <div class="address_text">{{ address.address }}</div>
<!--            <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">-->
<!--                <path d="M4.05755 15.3754L13.2227 6.14804L10.0175 2.94287L0.807451 12.1529C0.621665 12.3387 0.51646 12.5902 0.514584 12.8529L0.499938 14.9033C0.495608 15.5095 1.02733 15.9801 1.62852 15.9021L3.47666 15.6624C3.69677 15.6339 3.90113 15.5329 4.05755 15.3754Z" fill="#8E8E93" stroke="#8E8E93" stroke-linecap="round"/>-->
<!--                <path d="M15.2368 4.66603L11.7368 1.16603C11.7368 1.16603 13.7366 -0.333672 15.2368 1.16632C16.737 2.66632 15.2368 4.66603 15.2368 4.66603Z" fill="#8E8E93" stroke="#8E8E93" stroke-linecap="round"/>-->
<!--            </svg>-->
        </div>
        <div class="address_add" @click="openOverlay('address_adding_overlay')">
            <svg width="16" height="25" viewBox="0 0 16 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1.1836 10.9476L6.9692 23.525C7.32621 24.3011 8.42916 24.3011 8.78617 23.525L14.5718 10.9476C16.8173 6.06604 13.251 0.5 7.87769 0.5C2.50437 0.5 -1.06194 6.06603 1.1836 10.9476Z" fill="#FF5900" stroke="#FF5900" stroke-linecap="round"/>
            </svg>
            <div>Добавить адрес</div>
        </div>
    </div>
    <div class="address_adding_overlay" style="display: none">
        <div ref="map" class="yandex-map"></div>
        <div class="address_adding_overlay_footer">
            <address-component ref="addressComponent" v-model="address"/>
            <div class="address_adding_overlay_footer_buttons">
                <button @click="closeMapOverlay" class="inactive">Удалить</button>
                <button @click="save">Сохранить</button>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
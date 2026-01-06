<script>
import {openOverlay} from "@/utils.js";
import AddressComponent from "@/components/AddressComponent.vue";

export default {
    name: "AddressView",
    components: {AddressComponent},
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
        openOverlay,
        async initMap() {
            // Инициализация карты
            const {YMap, YMapDefaultSchemeLayer} = window.ymaps3;

            const map = new YMap(
                this.$refs.map,
                {
                    location: {
                        center: [37.588144, 55.733842],
                        zoom: 10
                    }
                }
            );

            map.addChild(new YMapDefaultSchemeLayer());
        }
    }
}
</script>

<template>
    <div class="address">
        <div v-for="el in 4">
            <div class="address_checkbox"></div>
            <div class="address_text">Самара, Московское шоссе, 316</div>
            <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4.05755 15.3754L13.2227 6.14804L10.0175 2.94287L0.807451 12.1529C0.621665 12.3387 0.51646 12.5902 0.514584 12.8529L0.499938 14.9033C0.495608 15.5095 1.02733 15.9801 1.62852 15.9021L3.47666 15.6624C3.69677 15.6339 3.90113 15.5329 4.05755 15.3754Z" fill="#8E8E93" stroke="#8E8E93" stroke-linecap="round"/>
                <path d="M15.2368 4.66603L11.7368 1.16603C11.7368 1.16603 13.7366 -0.333672 15.2368 1.16632C16.737 2.66632 15.2368 4.66603 15.2368 4.66603Z" fill="#8E8E93" stroke="#8E8E93" stroke-linecap="round"/>
            </svg>
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
            <address-component />
            <div class="address_adding_overlay_footer_buttons">
                <button class="inactive">Удалить</button>
                <button>Сохранить</button>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
<script>
import {openOverlay} from "@/utils.js";

export default {
    name: "ProductView",
    data () {
        return {

        }
    },
    methods: {
        openOverlay,
        openInfo () {
            let overlay = this.$refs.overlay;
            overlay.style.display = "";

            requestAnimationFrame(() => {
                overlay.style.opacity = "1";
            })

            window.Telegram.WebApp.BackButton.offClick(window.backByQueryFunction);
            window.Telegram.WebApp.BackButton.onClick(this.closeInfo);
            window.Telegram.WebApp.BackButton.show();

            document.body.style.overflow = "hidden";
        },
        closeInfo() {
            window.Telegram.WebApp.BackButton.offClick(this.closeInfo);
            window.Telegram.WebApp.BackButton.onClick(window.backByQueryFunction);
            document.body.style.overflow = "";

            let overlay = this.$refs.overlay;
            overlay.style.opacity = "";
            overlay.addEventListener("transitionend", () => {
                overlay.style.display = "none";
            }, {once: true})
        }
    }
}
</script>

<template>
    <div style="display: none" class="productView_settings_overlay" ref="overlay">
        <div class="productView_settings_overlay_block">
            <div class="productView_settings_overlay_title">Убрать ингредиенты</div>
            <div class="productView_settings_overlay_container">
                <div v-for="ingredient in ['Моцарелла', 'Пармезан', 'Сыр с плесенью', 'Сыр эмменталь']">
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.5 0.5L11.5 11.5" stroke="white" stroke-linecap="round"/>
                        <path d="M11.5 0.5L0.499999 11.5" stroke="white" stroke-linecap="round"/>
                    </svg>
                    <div>{{ ingredient }}</div>
                </div>
            </div>
        </div>
        <div class="productView_settings_overlay_block">
            <div class="productView_settings_overlay_title">Аллергены</div>
            <div class="productView_settings_overlay_container">
                <div v-for="allergen in ['Глютен', 'Лактоза']">
                    {{ allergen }}
                </div>
            </div>
        </div>
        <div class="productView_settings_overlay_block">
            <div class="productView_settings_overlay_title">О продукте</div>
            <div class="productView_settings_overlay_info">
                <div class="productView_settings_overlay_info_selector">
                    <div class="active">100 г</div>
                    <div>Вся пицца</div>
                </div>
                <div class="productView_settings_overlay_info_main">
                    <div>
                        <div>344.1</div>
                        <span>ккал</span>
                    </div>
                    <div>
                        <div>14.0</div>
                        <span>белки</span>
                    </div>
                    <div>
                        <div>19.7</div>
                        <span>жиры</span>
                    </div>
                    <div>
                        <div>28.1</div>
                        <span>углеводы</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="productView">
        <img src="/pizza.png" alt="" @click="openInfo">
        <div class="productView_info">
            <div class="productView_title">Пицца 4 сыра</div>
            <div class="productView_description">Неаполитанская пицца. Состав - моцарелла, сыр «Эмменталь», сыр с голубой плесенью, пармезан. Диаметр 30 см.</div>
        </div>
        <button>+ 552</button>
    </div>
</template>

<style scoped>

</style>
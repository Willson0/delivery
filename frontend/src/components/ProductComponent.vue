<script>
import {toLink} from "@/utils.js";
import ProductView from "@/views/ProductView.vue";

export default {
    name: "ProductComponent",
    components: {ProductView},
    props: {
        onlyImage: {
            type: Boolean,
            default: false,
        }
    },
    methods: {
        toLink,
        openProduct () {
            document.body.style.overflow = "hidden";
            window.Telegram.WebApp.BackButton.offClick(window.backByQueryFunction);
            window.Telegram.WebApp.BackButton.onClick(this.closeProduct);
            window.Telegram.WebApp.BackButton.show();
            window.closeProductFunction = this.closeProduct;

            let view = this.$refs.productView.querySelector(".productView");
            this.$refs.productView.style.display = "";
            view.style.transform = "translateY(125%)";
            view.style.position = "fixed";

            let background = this.$refs.productBackground;
            background.style.display = "";
            background.style.opacity = "0";

            requestAnimationFrame(() => {
                view.style.transform = "";
                background.style.opacity = "1";
            })
        },
        closeProduct () {
            document.body.style.overflow = "";
            window.Telegram.WebApp.BackButton.offClick(this.closeProduct);
            window.Telegram.WebApp.BackButton.onClick(window.backByQueryFunction);
            window.closeProductFunction = null;

            let view = this.$refs.productView.querySelector(".productView");
            view.style.transform = "translateY(125%)";

            let background = this.$refs.productBackground;
            background.style.opacity = "0";

            view.addEventListener("transitionend", () => {
                this.$refs.productView.style.display = "none";
                view.style.position = "";
                background.style.display = "none";
            }, {once: true})
        },
        addToCart () {
            this.$emit('test');
        }
    },
}
</script>

<template>
    <teleport to="body">
        <div ref="productBackground" style="display: none" class="productBackground background"></div>
        <div ref="productView" style="display: none; max-width: 0">
            <product-view />
        </div>
    </teleport>
    <div class="product" v-if="!onlyImage" @click="openProduct">
        <img src="/pizza.png" alt="">
        <div class="product_name">Пицца 4 сыра</div>
        <div class="product_weight">400 г</div>
        <div class="product_price" @click.stop="addToCart">
            <div class="product_price_main">552 ₽</div>
            <div class="product_price_sale">
                <div class="product_price_sale_price">650 ₽</div>
                <svg viewBox="0 0 30 10" class="line">
                    <line x1="0" y1="10" x2="30" y2="0" stroke="white" stroke-width="1"/>
                </svg>
            </div>
        </div>
    </div>
    <img src="/pizza.png" v-else @click="openProduct" alt="">
</template>

<style scoped>

</style>
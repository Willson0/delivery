<script>
import {addToCart, deepParse, toLink} from "@/utils.js";
import ProductView from "@/views/ProductView.vue";
import config from "@/config.json"
import LogoSVG from "@/svg/LogoSVG.vue";

export default {
    name: "ProductComponent",
    components: {LogoSVG, ProductView},
    data () {
        return {
            config: config,
        }
    },
    props: {
        onlyImage: {
            type: Boolean,
            default: false,
        },
        product: {
            type: Object,
            required: true
        },
        isBonus: {
            type: Boolean,
            default: false,
        },
        clickable: {
            type: Boolean,
            default: true,
        }
    },
    methods: {
        addToCart,
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
    },
    computed: {
        user () {
            return this.$store.state.user;
        },
    }
}
</script>

<template>
    <teleport to="body">
        <div ref="productBackground" style="display: none" class="productBackground background"></div>
        <div ref="productView" style="display: none; max-width: 0">
            <product-view :product="product"/>
        </div>
    </teleport>
    <div class="product" v-if="!onlyImage" @click="clickable ? openProduct() : null">
        <img :src="config.storage + product.image" alt="">
        <div class="product_name">{{ product.name }}</div>
        <div class="product_weight" :style="{'visibility': product.measure == null ? 'hidden' : 'unset'}">{{ product.measure }} г</div>
        <div class="product_price" @click.stop="addToCart(product.id, 1, isBonus)">
            <div class="product_price_main">{{ product.priceDiscount !== 0 ? product.priceDiscount : product.price }}
                <span v-if="!isBonus">₽</span>
                <svg v-else width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2.71099 15.5325C2.33642 13.9419 2.3328 12.5196 2.95889 11.2123C3.45289 10.1822 4.09345 9.21358 4.78288 8.32418C5.53563 7.35356 5.59535 7.07228 4.78288 6.14128C3.74784 4.95672 1.27966 4.6794 0.152338 5.62031C-0.343468 3.42948 0.378527 2.26078 2.20794 2.10231C2.89375 2.04288 3.34251 1.81706 3.73698 1.14753C4.36669 0.075891 5.50668 0.075891 6.50372 0.0184462C9.89475 -0.175678 12.5167 1.14754 14.0403 4.69327C15.4409 7.95376 16.0652 11.3608 15.9946 14.9402C15.9892 15.2275 15.924 15.5147 15.8607 16C14.1561 14.7521 12.7266 13.2426 10.8574 12.589C10.7923 12.6524 10.7271 12.7138 10.6638 12.7771C10.9985 13.3892 11.3351 14.0033 11.7314 14.7303C10.177 14.9284 8.83437 14.5084 7.51524 13.9657C7.04115 13.7715 6.52182 13.5576 6.16896 13.1872C5.39087 12.3631 4.79554 12.6603 4.22555 13.4051C3.75146 14.0231 3.31717 14.6768 2.71099 15.5305V15.5325ZM5.53201 2.69458C5.48135 2.81938 5.43249 2.94417 5.38183 3.06699C5.70573 3.32252 6.02963 3.78009 6.35353 3.78207C6.80048 3.78207 7.26915 3.48098 7.6799 3.21555C7.80657 3.13235 7.8699 2.6926 7.78486 2.52225C7.70705 2.36378 7.37772 2.25681 7.18229 2.28851C6.62496 2.37963 6.0803 2.55196 5.53021 2.6926L5.53201 2.69458Z" fill="white"/>
                </svg>
            </div>
            <div class="product_price_sale" v-if="product.priceDiscount !== 0">
                <div class="product_price_sale_price">{{ product.price }} ₽</div>
                <svg viewBox="0 0 30 10" class="line">
                    <line x1="0" y1="10" x2="30" y2="0" stroke="white" stroke-width="1"/>
                </svg>
            </div>
        </div>
    </div>
    <img :src="config.storage + product.image" v-else @click="clickable ? openProduct() : null" alt="">
</template>

<style scoped>

</style>
<script>
import ProductComponent from "@/components/ProductComponent.vue";
import {toLink} from "@/utils.js";
import CartView from "@/views/CartView.vue";

export default {
    name: "HomeView",
    components: {CartView, ProductComponent},
    data () {
        return {
            selectedCategory: 1,
            fullCart: false,
        }
    },
    watch: {
        selectedCategory () {
            let nav = this.$refs.navigation;
            nav.style.height = nav.clientHeight + "px";
        },
        fullCart () {
            requestAnimationFrame(() => {
                let width = document.querySelector('.home_deliveryTime_cart').clientWidth;
                document.querySelector(".home_deliveryTime").style.width = width + "px";
            })
        }
    },
    methods: {
        toLink,
        openCart () {
            document.body.style.overflow = "hidden";
            window.Telegram.WebApp.BackButton.offClick(window.backByQueryFunction);
            window.Telegram.WebApp.BackButton.onClick(this.closeCart);
            window.Telegram.WebApp.BackButton.show();

            let cart = document.querySelector(".cart");
            let footer = cart.querySelector(".cart_footer");
            cart.style.display = "";
            cart.style.transform = "translateY(100%)";
            cart.style.position = "fixed";

            let background = this.$refs.cartBackground;
            background.style.display = "";

            requestAnimationFrame(() => {
              cart.style.transform = "";
              footer.style.opacity = "0";
              background.style.opacity = "1";
              cart.addEventListener("transitionend", () => {
                  requestAnimationFrame(() => {
                      footer.style.opacity = "1";
                      // footer.addEventListener("transitionend", () => {
                      //     toLink('cart');
                      // })
                  })
              }, {once: true})
            })
        },
        closeCart() {
            document.body.style.overflow = "";
            window.Telegram.WebApp.BackButton.offClick(this.closeCart);
            window.Telegram.WebApp.BackButton.onClick(window.backByQueryFunction);

            let cart = document.querySelector(".cart");
            let footer = cart.querySelector(".cart_footer");
            footer.style.opacity = "0";

            let background = this.$refs.cartBackground;
            background.style.opacity = "";

            cart.style.transform = "translateY(100%)";
            cart.addEventListener("transitionend", () => {
                background.style.display = "none";
                cart.style.display = "none";
            }, {once: true})
        }
    },
}
</script>

<template>
    <div ref="cartBackground" style="display: none" class="cart_background background"></div>
    <cart-view style="display: none; z-index: 999999; width: 100vw; max-height: 100vh;" @close="closeCart()"/>

    <button @click="toLink('order')" style="color: white;">To order</button>
    <button @click="toLink('auth')" style="color: white;">To auth</button>
    <div class="home">
        <div class="home_account">
            <div class="home_account_name">Vanya</div>
            <div class="home_account_info" @click="toLink('profile')">
                <div class="home_account_info_bank">231</div>
                <img src="https://img.freepik.com/premium-psd/3d-render-avatar-character_23-2150611783.jpg?semt=ais_hybrid&w=740" alt="">
            </div>
        </div>
        <div class="home_stories_slider">
            <div v-for="el in 10">
                <div class="home_story_background"></div>
                <img src="/meat.jpg" alt="">
                <div class="home_story_title">Ужинайте вкусно!</div>
            </div>
        </div>
        <div class="home_nav" ref="navigation">
            <div :class="{active: el === selectedCategory}" @click="selectedCategory = el"
                 v-for="el in 10">
                <img draggable="false" src="/star.png" alt="">
                <div>Популярное</div>
            </div>
        </div>
        <div class="home_products">
            <product-component @test="fullCart = true" v-for="el in 11" />
        </div>
        <div class="home_deliveryTime">
            <div class="home_deliveryTime_time" v-show="!fullCart">Доставка от 35-45 минут</div>
            <div @click="openCart" class="home_deliveryTime_cart" v-show="fullCart">
                <img src="/cart.png" alt="">
                <div>552 ₽</div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
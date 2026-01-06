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
        }
    },
    watch: {
        selectedCategory () {
            let nav = this.$refs.navigation;
            nav.style.height = nav.clientHeight + "px";
        }
    },
    methods: {
        toLink,
        openCart () {
            let cart = document.querySelector(".cart");
            cart.
        }
    }
}
</script>

<template>
    <cart-view style="display: none"/>

    <button @click="openCart" style="color: white;">To cart</button>
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
            <product-component v-for="el in 11" />
        </div>
        <div class="home_deliveryTime">Доставка от 35-45 минут</div>
    </div>
</template>

<style scoped>

</style>
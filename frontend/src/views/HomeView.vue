<script>
import ProductComponent from "@/components/ProductComponent.vue";
import {toLink} from "@/utils.js";

export default {
    name: "HomeView",
    methods: {toLink},
    components: {ProductComponent}
}
</script>

<template>
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
        <div class="home_nav">
            <div :class="{active: el === 1}" v-for="el in 10">
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
<script>
import ProductComponent from "@/components/ProductComponent.vue";
import {closeCart, openCart, toLink} from "@/utils.js";
import CartView from "@/views/CartView.vue";
import config from "@/config.json"

export default {
    name: "HomeView",
    components: {CartView, ProductComponent},
    data () {
        return {
            selectedCategory: -1,
            config: config,
            icons: {
                'пицца': 'pizza-section.webp',
                'бургеры': 'burger-section.webp',
                'роллы': 'sushi-section.webp',
                'шашлык': 'barbecue-section.webp',
                'напитки': 'drink-section.webp'
            },

            mouseDown: false,
            startX: 0,
            scrollLeft: 0,
            scrollElement: null,
            isDragging: false,
            constX: 0,
        }
    },
    mounted () {
        this.updateCartWidth(0);
    },
    watch: {
        selectedCategory () {
            let nav = this.$refs.navigation;
            nav.style.height = nav.clientHeight + "px";
        },
        'user.cart' (val) {
            this.updateCartWidth();
        }
    },
    methods: {
        toLink,
        closeCart,
        openCart,
        openLink (url) {
            window.Telegram.WebApp.openLink(url);
        },
        mousedown(ev) {
            document.body.classList.add("grabbing");
            this.scrollElement = ev.target.closest('.slider');

            this.mouseDown = true;
            this.startX = ev.pageX;
            this.constX = ev.pageX;

            window.addEventListener("mousemove", this.mousemove);
            window.addEventListener("mouseup", this.mouseup);
        },
        mousemove (ev) {
            if (!this.mouseDown) return;

            ev.preventDefault();
            let slider = this.scrollElement;

            const walk = (ev.pageX - this.startX) * 1; // 1 = чувствительность
            slider.scrollLeft -= walk;

            if (Math.abs(this.constX - ev.pageX) > 10) this.isDragging = true;

            this.startX = ev.pageX;
        },
        mouseup (ev) {
            document.body.classList.remove("grabbing");
            this.mouseDown = false;

            setTimeout(() => {
                this.isDragging = false;
            }, 200);

            window.removeEventListener("mousemove", this.mousemove);
            window.removeEventListener("mouseup", this.mouseup);
        },
        updateCartWidth (transition = 1) {
            if (this.fullCart)
                requestAnimationFrame(() => {
                    let width = document.querySelector('.home_deliveryTime_cart').clientWidth;

                    let el = document.querySelector(".home_deliveryTime");
                    if (transition === 0) el.style.transition = "0s";
                    el.style.width = width + "px";
                    requestAnimationFrame(() => {
                        el.style.transition = "";
                    })
                })
            else document.querySelector(".home_deliveryTime").style.width = null;
        }
    },
    computed: {
        avatar () {
            return window.Telegram.WebApp.initDataUnsafe?.user?.photo_url;
        },
        name () {
            return window.Telegram.WebApp.initDataUnsafe?.user?.first_name
                ?? window.Telegram.WebApp.initDataUnsafe?.user?.last_name
                ?? window.Telegram.WebApp.initDataUnsafe?.user?.username;
        },
        user () {
            return this.$store.state.user;
        },
        content () {
            if (this.selectedCategory === -1) {
                return this.user.products?.sort((a,b) => b.rating - a.rating)
            } else {
                return this.user.products?.filter(product => product.sectionId === this.selectedCategory)
            }
        },
        fullCart () {
            return (this.user.cart?.length > 0);
        }
    }
}
</script>

<template>
    <div ref="cartBackground" style="display: none" class="cart_background background"></div>
    <div ref="cartOverlay" class="cart_overlay" style="display: none; z-index: 999999; width: 100vw; max-height: 100vh;">
        <cart-view @close="closeCart()"/>
    </div>

    <div class="home">
        <div class="home_account">
            <div class="home_account_name">{{ name }}</div>
            <div class="home_account_info" @click="toLink('profile')">
                <div class="home_account_info_bank">{{ user.bonus }}</div>
                <img :src="avatar" alt="">
            </div>
        </div>
        <div class="home_stories_slider slider" @mousedown="mousedown">
            <div v-for="ad in user.ads" @click="this.isDragging ? null : openLink(ad.link)">
                <div class="home_story_background"></div>
                <img :src="config.localStorage + ad.picture" alt="">
                <div class="home_story_title">{{ ad.text }}</div>
            </div>
        </div>
        <div class="home_nav slider" ref="navigation" @mousedown="mousedown">
            <div :class="{active: -1 === selectedCategory}" @click="this.isDragging ? null : selectedCategory = -1">
                <img draggable="false" src="/star.webp" alt="">
                <div>Популярное</div>
            </div>
            <div :class="{active: section.id === selectedCategory}" @click="this.isDragging ? null : selectedCategory = section.id"
                 v-for="section in user.sections">
                <img draggable="false" :src="'/' + icons[section.name.toLowerCase()]" alt="">
                <div>{{ section.name }}</div>
            </div>
        </div>
        <div class="home_products">
            <product-component :product="el" v-for="el in content" />
        </div>
        <div class="home_deliveryTime" @click="fullCart ? openCart() : null">
            <div class="home_deliveryTime_time" v-show="!fullCart">Доставка от 35-45 минут</div>
            <div class="home_deliveryTime_cart" v-show="fullCart">
                <img src="/cart.webp" alt="">
                <div>{{ user.cartSum }} ₽</div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
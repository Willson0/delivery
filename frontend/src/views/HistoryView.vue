<script>
import ProductComponent from "@/components/ProductComponent.vue";
import {addToCart, closeCart, endLoading, notify, openCart, toLink, whatError} from "@/utils.js";
import axios from "axios";
import config from "@/config.json";
import CartView from "@/views/CartView.vue";

export default {
    name: "HistoryView",
    components: {CartView, ProductComponent},
    computed: {
        user () {
            return this.$store.state.user;
        }
    },
    data () {
        return {
            history: [],

            mouseDown: false,
            startX: 0,
            scrollLeft: 0,
            scrollElement: null,
            isDragging: false,
            constX: 0,
        }
    },
    async mounted () {
        await axios.post(config.backend + "order/history", {
            initData: window.Telegram.WebApp.initData,
        }).then((response) => {
            try {
                endLoading('historyLoading');
            } catch (e) {}
            this.history = response.data;
        }).catch((error) => notify(whatError(error), 1))
    },
    methods: {
        closeCart,
        openCart,
        addToCart,
        unixToPrettyDate (unixTime) {
            const date = new Date(unixTime * 1000);
            const months = [
                "января", "февраля", "марта", "апреля", "мая", "июня",
                "июля", "августа", "сентября", "октября", "ноября", "декабря"
            ];
            const day = date.getDate();
            const month = months[date.getMonth()];
            const year = date.getFullYear();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${day} ${month} ${year} г. в ${hours}:${minutes}`;
        },
        getProducts (products) {
            if (!this.user.id) return;

            let ar = [];
            products.split('\n').forEach((product) => {
                let name = product.split(':')[0].slice(3);
                ar.push(this.user.products.filter(a => a.name.includes(name))[0] ?? name);
            });
            return ar;
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
        repeat (ar) {
            let newCart = [];
            ar.filter(a => typeof a === 'object').forEach(a => {
                newCart.push({
                    id: a.id, count: 1, isBonus: 0,
                })
            })
            let last = null;
            if (newCart.length > 0) {
                last = newCart.slice(-1)[0];
                newCart = newCart.slice(0, -1);
            }

            let newUser = {...this.user};
            newUser.cart = newCart;
            this.$store.dispatch("updateUser", newUser);
            this.addToCart(last?.id, 0, 0);

            this.openCart();
        }
    }
}
</script>

<template>
    <div ref="cartBackground" style="display: none" class="cart_background background"></div>
    <div ref="cartOverlay" class="cart_overlay" style="display: none; z-index: 999999; width: 100vw; max-height: 100vh;">
        <cart-view @close="closeCart()"/>
    </div>

    <div class="loading historyLoading"></div>
    <div class="history">
        <div v-for="order in history">
            <div class="history_date">{{ unixToPrettyDate(order.dateCreate) }}</div>
            <hr>
            <div class="history_delivery">
                <div class="history_delivery_title">Доставка</div>
                <div class="history_delivery_address">{{ order.address }}</div>
            </div>
            <hr>
            <div class="history_summa">
                <div class="history_summa_title">Сумма</div>
                <div class="history_summa_value">{{(order.priceFull).toString().split('').reverse().join('').match(/.{1,3}/g)
                    .join(' ').split('').reverse().join('').trim() }} ₽</div>
            </div>
            <hr>
            <div class="history_products_container">
                <div class="history_product_transparent"></div>
                <div class="history_product_transparent right"></div>
                <div class="history_products slider" @mousedown.prevent="mousedown">
                    <template v-for="product in getProducts(order.products)">
                        <div v-if="typeof product === 'string'"><span>{{product}}</span></div>
                        <product-component :clickable="!isDragging" v-else :product="product" :only-image="true"/>
                    </template>
                </div>
            </div>
            <hr>
            <button @click="repeat(getProducts(order.products))">Повторить заказ</button>
        </div>
    </div>
</template>

<style scoped>

</style>
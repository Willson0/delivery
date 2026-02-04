<script>
import ProductComponent from "@/components/ProductComponent.vue";
import {deepParse, endLoading, notify, toLink, whatError} from "@/utils.js";
import config from "@/config.json";
import axios from 'axios';
import statuses from "@/statuses.json";

export default {
    name: "AcceptView",
    data () {
        return {
            order: {},
            interval: null,
            statuses: statuses,
            firstLoading: true,
        }
    },
    async mounted () {
        this.checkOrder();
        this.interval = setInterval(this.checkOrder, 5*1000);
    },
    unmounted () {
        clearInterval(this.interval);
    },
    methods: {
        toLink,
        async checkOrder () {
            if (!this.user.id || !this.user.orders[0]) return;

            let order = this.user.orders[0];
            await axios.post(config.backend + "order/" + order.id, {
                initData: window.Telegram.WebApp.initData,
            }).then((response) => {
                if (this.firstLoading) {
                    this.firstLoading = false;
                    endLoading('acceptLoading');
                }

                this.order = deepParse(response.data);
                if (this.order.status === 16) {
                    notify('Ваш заказ был отменен', 1);
                    this.removeOrder();
                }
                if (this.order.status === 18) {
                    notify("Ваш заказ был успешно доставлен!");
                    this.removeOrder();
                }
            }).catch((error) => {
                // notify(whatError(error), 1)
                notify('Ваш заказ был отменен', 1);
                this.removeOrder();
            })
        },
        getProduct (id) {
            return this.user?.products?.find(product => product.id === Number(id));
        },
        getProductsName (count) {
            const mod10 = count % 10;
            const mod100 = count % 100;

            if (mod10 === 1 && mod100 !== 11) return "товар";
            else if (mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20)) return "товара"
            else return "товаров";
        },
        async cancelOrder () {
            if (!confirm("Вы уверены, что хотите отменить заказ?")) return;

            this.removeOrder();
            clearInterval(this.interval);

            await axios.post(config.backend + "order/" + this.order.id + "/cancel", {
                initData: window.Telegram.WebApp.initData,
            }).then((response) => {
            }).catch((error) => notify(whatError(error), 1))
        },
        removeOrder () {
            let newUser = {...this.user};
            newUser.orders = newUser.orders.filter(a => a.id !== this.order.id);
            this.$store.dispatch("updateUser", newUser);

            if (newUser.orders.length > 0) this.order = newUser.orders[0];
            else toLink('home');
        }
    },
    computed: {
        user () {
            return this.$store.state.user;
        }
    },
    components: {ProductComponent},
}
</script>

<template>
    <div class="loading acceptLoading"></div>
    <div class="accept" v-if="order.id">
        <div class="accept_header">
            <div>{{ statuses[order.status] }}</div>
<!--            <div>20 мин</div>-->
        </div>
        <div class="accept_delivery">
            <div class="accept_delivery_title">Доставим по адресу</div>
            <div class="accept_delivery_text">{{ order.address }}</div>
        </div>
        <div class="accept_products">
            <div class="accept_products_title">{{ order.products.length }} {{ getProductsName(order.products.length) }} на
                {{ order.priceFull.toString().split('').reverse().join('').match(/.{1,3}/g)
                    .join(' ').split('').reverse().join('').trim() }} ₽</div>
            <div class="accept_products_list_container">
                <div class="accept_products_list_transparent"></div>
                <div class="accept_products_list_transparent right"></div>
                <div class="accept_products_list">
                    <product-component :product="getProduct(product)" v-for="product in order.products" :only-image="true"/>
                </div>
            </div>
        </div>
        <button v-if="order.status === 0" @click="cancelOrder">Отменить заказ</button>
    </div>
</template>

<style scoped>

</style>
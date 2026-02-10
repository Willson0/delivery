<script>
import AddressComponent from "@/components/AddressComponent.vue";
import {notify, toLink, whatError} from "@/utils.js";
import axios from "axios";
import config from "@/config.json";

export default {
    name: "OrderView",
    components: {AddressComponent},
    data () {
        return {
            delivery: "",
            deliveriesData: {'flat': 'В квартиру', 'house': 'В частный дом', 'office': 'В офис'},
            isOnline: true,
            isLoading: false,
        }
    },
    async mounted () {
        this.delivery = "flat";
    },
    methods: {toLink,
        async createOrder () {
            if (this.isLoading) return;

            this.isLoading = true;

            let coords = this.$refs.addressComponent.getCoords();
            console.log(coords);
            if (coords == null || coords.length === 0) return this.isLoading = false;

            await axios.post(config.backend + "order", {
                initData: window.Telegram.WebApp.initData,
                address: {
                    address: this.$refs.addressComponent.getString(),
                    latitude: coords[0],
                    longitude: coords[1],
                    commentAddress: this.$refs.addressComponent.getComment() ?? "",
                },
                paymentType: 0,
            }).then((response) => {
                window.Telegram.WebApp.openLink(config.payment + "?id=" + response.data.id + "&method=2");

                let newUser = {...this.user};
                newUser.orders[0] = response.data.id;
                this.$store.dispatch("updateUser", newUser);
            }).catch((error) =>
                notify(whatError(error), 1)
            ).finally(() => this.isLoading = false);
            // toLink('accept')
        },
    },
    watch: {
        delivery (val) {
            this.$nextTick(() => {
                let background = this.$refs.deliveryBackground;
                let selector = this.$refs[this.delivery][0];
                background.style.width = selector.clientWidth + "px";

                let parentRect = selector.parentElement.getBoundingClientRect();
                let childRect = selector.getBoundingClientRect();
                let leftOffset = childRect.left - parentRect.left;

                background.style.left = leftOffset + "px";
            })
        },
        isOnline () {
            let background = this.$refs.paymentBackground;
            if (this.isOnline) {
                background.style.left = "0%";
            } else background.style.left = "calc(50% - 4px)";
        },
    },
    computed: {
        user () {
            return this.$store.state.user;
        }
    }
}
</script>

<template>
    <div class="order">
        <div class="order_main">
            <div class="order_to">
                <div class="order_title">Куда</div>
                <div class="order_to_selector">
                    <div class="order_to_selector_background" ref="deliveryBackground"></div>
                    <div v-for="(text, value) in deliveriesData" :ref="value"
                         @click="delivery = value" :class="{active: delivery === value}">
                        {{ text }}
                    </div>
                </div>
                <address-component ref="addressComponent" :user-selected="true"/>
            </div>
            <hr>
            <div class="order_payment">
                <div class="order_title">Оплата</div>
                <div class="order_to_selector">
                    <div class="order_to_selector_background payment_selector" ref="paymentBackground"></div>
                    <div :class="{active: isOnline}" @click="isOnline = true">Онлайн</div>
                    <div :class="{active: !isOnline}" @click="isOnline = false">Наличные</div>
                </div>
                <div class="order_description">Оплата происходит через АО “Тинькофф Банк” с использование банковских карт платёжных систем:</div>
            </div>
            <div @click="createOrder" class="order_footer">
                <button>Оплатить {{ user.cartSum + 200 }} Р</button>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
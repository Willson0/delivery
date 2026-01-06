<script>
import AddressComponent from "@/components/AddressComponent.vue";

export default {
    name: "OrderView",
    components: {AddressComponent},
    data () {
        return {
            delivery: "",
            deliveriesData: {'flat': 'В квартиру', 'house': 'В частный дом', 'office': 'В офис'},
            isOnline: true,
        }
    },
    async mounted () {
        this.delivery = "flat";
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
                <address-component />
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
            <div class="order_footer">
                <button>Оплатить 1 000 Р</button>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
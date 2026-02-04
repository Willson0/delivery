<script>
import ProductComponent from "@/components/ProductComponent.vue";

export default {
    name: "HistoryView",
    components: {ProductComponent},
    computed: {
        user () {
            return this.$store.state.user;
        }
    }
}
</script>

<template>
    <div class="history">
        <div v-for="d in 10">
            <div class="history_date">30 ноября 2025 г. в 16:30</div>
            <hr>
            <div class="history_delivery">
                <div class="history_delivery_title">Доставка</div>
                <div class="history_delivery_address">Самара, Садовая улица, 230</div>
            </div>
            <hr>
            <div class="history_summa">
                <div class="history_summa_title">Сумма</div>
                <div class="history_summa_value">1 000 Р</div>
            </div>
            <hr>
            <div class="history_products_container">
                <div class="history_product_transparent"></div>
                <div class="history_product_transparent right"></div>
                <div class="history_products">
                    <product-component v-for="el in 10" :only-image="true"/>
                </div>
            </div>
            <hr>
            <button>Повторить заказ</button>
        </div>
    </div>
</template>

<style scoped>

</style>
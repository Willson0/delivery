<script>
export default {
    name: "ProductComponent"
}
</script>

<template>
    <div class="product">
        <img src="/pizza.png" alt="">
        <div class="product_name">Пицца 4 сыра</div>
        <div class="product_weight">400 г</div>
        <div class="product_price">
            <div class="product_price_main">552 ₽</div>
            <div class="product_price_sale">
                <div class="product_price_sale_price">650 ₽</div>
                <svg viewBox="0 0 30 10" class="line">
                    <line x1="0" y1="10" x2="30" y2="0" stroke="white" stroke-width="1"/>
                </svg>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
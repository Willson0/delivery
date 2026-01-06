<script>
export default {
    name: "AddressComponent"
}
</script>

<template>
    <div class="addressComponent">
        <div>
            <div>
                <label for="">Город, улица и дом</label>
                <input type="text" placeholder="Адрес">
            </div>
        </div>
        <div>
            <div>
                <label for="">Подъезд</label>
                <input type="text" placeholder="Подъезд">
            </div>
            <div>
                <label for="">Домофон</label>
                <input type="text" placeholder="Домофон">
            </div>
        </div>
        <div>
            <div>
                <label for="">Этаж</label>
                <input type="text" placeholder="Этаж">
            </div>
            <div>
                <label for="">Квартира</label>
                <input type="text" placeholder="Квартира">
            </div>
        </div>
        <div>
            <div class="addressComponent_without">
                <input type="text" placeholder="Комментарий для курьера">
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
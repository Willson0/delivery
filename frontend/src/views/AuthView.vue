<script>
import LogoSVG from "@/svg/LogoSVG.vue";

export default {
    name: "AuthView",
    components: {LogoSVG},
    data () {
        return {
            next: false,
        }
    }
}
</script>

<template>
    <div class="auth">
        <div class="authMain">
            <logo-s-v-g />
            <div class="authMain_title">Введите номер телефона</div>
            <div class="authMain_input" v-if="next === false">
                <span>+ 7</span>
                <div>
                    <input v-for="el in 3" type="number" placeholder="0">
                </div>
                <div>
                    <input v-for="el in 2" type="number" placeholder="0">
                </div>
                <div>
                    <input v-for="el in 2" type="number" placeholder="0">
                </div>
            </div>
            <div class="authMain_input code" v-else>
                <div v-for="bl in 2">
                    <div v-for="inp in 3">
                        <input type="number">
                        <div></div>
                    </div>
                </div>
            </div>
            <div class="authMain_approval" v-if="next === false">
                <div class="authMain_checkbox"></div>
                <div class="authMain_approval_text">Соглашаюсь на <a>Политику конфиденциальности</a></div>
            </div>
        </div>
        <button @click="next = true">Далее</button>
    </div>
</template>

<style scoped>

</style>
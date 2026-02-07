<script>
import {openOverlay, toLink} from "@/utils.js";
import AlleregensComponent from "@/components/AlleregensComponent.vue";

export default {
    name: "ProfileView",
    components: {AlleregensComponent},
    data () {
        return {
            mouseDown: false,
            startX: 0,
            scrollLeft: 0,
            scrollElement: null,
            isDragging: false,
            constX: 0,
        }
    },
    methods: {openOverlay, toLink,
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
        }
    }
}
</script>

<template>
    <alleregens-component />
    <div class="profile">
        <div class="profile_account">
            <img class="profile_account_avatar" :src="avatar" alt="">
            <div class="profile_account_info">
                <div class="profile_account_info_name">{{ name }}</div>
<!--                <div class="profile_account_info_status">Статус</div>-->
            </div>
<!--            <img class="profile_account_settings" src="/settings.webp" alt="">-->
        </div>
        <div class="profile_widgets slider" @mousedown.prevent="mousedown">
            <div @click="isDragging ? null : toLink('bonus')">
                <div class="profile_widget_text" style="font-weight: 700; font-size: 40px;">{{ user.bonus }}</div>
                <img src="/coins.webp" alt="" style="width: 100%; bottom: 0; left: 0;">
            </div>
            <div @click="isDragging ? null : toLink('address')">
                <div class="profile_widget_text">Адреса доставки</div>
                <img src="/house.webp" alt="" style="width: 85px; bottom: 0; right: 5px;">
            </div>
            <div @click="isDragging ? null : toLink('history')">
                <div class="profile_widget_text">История заказов</div>
                <img src="/clock.webp" alt="" style="width: 78px; bottom: -10px; right: 4px; transform: rotate(15deg)">
            </div>
        </div>
        <div class="profile_allergens">
            <div class="profile_allergens_title">Аллергены</div>
            <div class="profile_allergens_list">
                <button @click="openOverlay('allergens', 'allergens_background')">+ Добавить</button>
                <div v-for="al in user.allergens">{{ al }}</div>
            </div>
        </div>
<!--        <div class="profile_challenges">-->
<!--            <div class="profile_challenges_title">Челленджи</div>-->
<!--            <div class="profile_challenges_main">-->

<!--            </div>-->
<!--        </div>-->
    </div>
</template>

<style scoped>

</style>
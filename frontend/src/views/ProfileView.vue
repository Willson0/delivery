<script>
import {openOverlay, toLink} from "@/utils.js";
import AlleregensComponent from "@/components/AlleregensComponent.vue";
import config from "@/config.json";

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
            config: config,
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
        hasAchievement (achievement) {
            return this.user?.data?.[achievement.parameter] >= achievement?.value;
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
        unpinnedAchievements () {
            if (!this.user.pinned_achievements) return;
            let countPinned = this.user.achievements?.filter(ach => this.hasAchievement(ach) && this.user.pinned_achievements?.includes(ach.id))?.length ?? 0;

            let achs = this.user.achievements?.filter(ach => this.hasAchievement(ach) && !this.user.pinned_achievements?.includes(ach.id)).slice(0, 5 - countPinned);
            return achs;
        },
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
        <div @click="toLink('achievements')" class="profile_achievements">
            <div class="profile_achievements_header">
                <div>Витрина достижений</div>
                <svg width="10" height="19" viewBox="0 0 10 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1.5L8.29289 8.79289C8.68342 9.18342 8.68342 9.81658 8.29289 10.2071L1 17.5" stroke="white" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="profile_achievements_main">
                <template v-for="ach in user.achievements">
                    <svg v-if="hasAchievement(ach) && user.pinned_achievements?.includes(ach.id)"
                         width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <clipPath id="myCustomClip">
                                <path d="M10.7711 5.47958C18.0772 -1.82653 29.9228 -1.82653 37.2289 5.47958L42.5204 10.7711C49.8265 18.0772 49.8265 29.9228 42.5204 37.2289L37.2289 42.5204C29.9228 49.8265 18.0772 49.8265 10.7711 42.5204L5.47958 37.2289C-1.82653 29.9228 -1.82653 18.0772 5.47958 10.7711L10.7711 5.47958Z" />
                            </clipPath>
                        </defs>
                        <path d="M10.7711 5.47958C18.0772 -1.82653 29.9228 -1.82653 37.2289 5.47958L42.5204 10.7711C49.8265 18.0772 49.8265 29.9228 42.5204 37.2289L37.2289 42.5204C29.9228 49.8265 18.0772 49.8265 10.7711 42.5204L5.47958 37.2289C-1.82653 29.9228 -1.82653 18.0772 5.47958 10.7711L10.7711 5.47958Z"
                              fill="var(--accent)" id="myPlaceholder" style=""/>
                        <image v-if="ach.image" x="0" y="0" width="48" height="48"
                               :href="config.localStorage + ach.image"
                               clip-path="url(#myCustomClip)" preserveAspectRatio="xMidYMid slice"/>
                    </svg>
                </template>
                <svg v-for="ach in unpinnedAchievements" width="72" height="72" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <clipPath id="myCustomClip">
                            <path d="M10.7711 5.47958C18.0772 -1.82653 29.9228 -1.82653 37.2289 5.47958L42.5204 10.7711C49.8265 18.0772 49.8265 29.9228 42.5204 37.2289L37.2289 42.5204C29.9228 49.8265 18.0772 49.8265 10.7711 42.5204L5.47958 37.2289C-1.82653 29.9228 -1.82653 18.0772 5.47958 10.7711L10.7711 5.47958Z" />
                        </clipPath>
                    </defs>
                    <path d="M10.7711 5.47958C18.0772 -1.82653 29.9228 -1.82653 37.2289 5.47958L42.5204 10.7711C49.8265 18.0772 49.8265 29.9228 42.5204 37.2289L37.2289 42.5204C29.9228 49.8265 18.0772 49.8265 10.7711 42.5204L5.47958 37.2289C-1.82653 29.9228 -1.82653 18.0772 5.47958 10.7711L10.7711 5.47958Z"
                          fill="var(--accent)" id="myPlaceholder" style=""/>
                    <image x="0" y="0" width="48" height="48" :href="config.localStorage + ach.image"
                           clip-path="url(#myCustomClip)" preserveAspectRatio="xMidYMid slice"/>
                </svg>
                <svg v-for="ach in Math.max(0, 5 - (user.achievements?.filter(ach => hasAchievement(ach)).length ?? 0))" width="72" height="72" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <clipPath id="myCustomClip">
                            <path d="M10.7711 5.47958C18.0772 -1.82653 29.9228 -1.82653 37.2289 5.47958L42.5204 10.7711C49.8265 18.0772 49.8265 29.9228 42.5204 37.2289L37.2289 42.5204C29.9228 49.8265 18.0772 49.8265 10.7711 42.5204L5.47958 37.2289C-1.82653 29.9228 -1.82653 18.0772 5.47958 10.7711L10.7711 5.47958Z" />
                        </clipPath>
                    </defs>
                    <path d="M10.7711 5.47958C18.0772 -1.82653 29.9228 -1.82653 37.2289 5.47958L42.5204 10.7711C49.8265 18.0772 49.8265 29.9228 42.5204 37.2289L37.2289 42.5204C29.9228 49.8265 18.0772 49.8265 10.7711 42.5204L5.47958 37.2289C-1.82653 29.9228 -1.82653 18.0772 5.47958 10.7711L10.7711 5.47958Z"
                          fill="var(--accent)" id="myPlaceholder" style=""/>
                    <image x="0" y="0" width="48" height="48" :href="''"
                           clip-path="url(#myCustomClip)" preserveAspectRatio="xMidYMid slice"/>
                </svg>
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
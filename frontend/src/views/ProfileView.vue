<script>
import {openOverlay, toLink} from "@/utils.js";
import AlleregensComponent from "@/components/AlleregensComponent.vue";

export default {
    name: "ProfileView",
    components: {AlleregensComponent},
    methods: {openOverlay, toLink}
}
</script>

<template>
    <alleregens-component />
    <div class="profile">
        <div class="profile_account">
            <img class="profile_account_avatar" src="https://img.freepik.com/premium-psd/3d-render-avatar-character_23-2150611783.jpg?semt=ais_hybrid&w=740" alt="">
            <div class="profile_account_info">
                <div class="profile_account_info_name">Vanya</div>
                <div class="profile_account_info_status">Статус</div>
            </div>
            <img class="profile_account_settings" src="/settings.png" alt="">
        </div>
        <div class="profile_widgets">
            <div @click="toLink('bonus')">
                <div class="profile_widget_text" style="font-weight: 700; font-size: 40px;">231</div>
                <img src="/coins.png" alt="" style="width: 100%; bottom: 0; left: 0;">
            </div>
            <div @click="toLink('address')">
                <div class="profile_widget_text">Адреса доставки</div>
                <img src="/house.png" alt="" style="width: 85px; bottom: 0; right: 5px;">
            </div>
            <div @click="toLink('history')">
                <div class="profile_widget_text">История заказов</div>
                <img src="/clock.png" alt="" style="width: 78px; bottom: -10px; right: 4px; transform: rotate(15deg)">
            </div>
        </div>
        <div class="profile_allergens">
            <div class="profile_allergens_title">Аллергены</div>
            <div class="profile_allergens_list">
                <button @click="openOverlay('allergens', 'allergens_background')">+ Добавить</button>
                <div v-for="al in ['Глютен', 'Лактоза']">{{ al }}</div>
            </div>
        </div>
        <div class="profile_challenges">
            <div class="profile_challenges_title">Челленджи</div>
            <div class="profile_challenges_main">

            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
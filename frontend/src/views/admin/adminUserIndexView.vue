<script>
import adminnav from "@/components/adminnav.vue";
import axios from "axios";
import config from "@/config.json";
import {notify, removeLoading} from "@/assets/admin.js";
import {deepParse, getDate, whatError} from "../../utils.js";

export default {
    name: "adminShowView.vue",
    components: {adminnav},
    data() {
        return {
            user: {},
            showModal: false,
            currentImageIdx: 0,
            isDeleted: false,
            config: config,
            bonuses: 0,
        }
    },
    methods: {
        getDate,
        formatDate(dateStr) {
            if (!dateStr) return;

            const [datePart] = dateStr.split(' ');
            const [year, month, day] = datePart.split('-');
            return `${day}.${month}.${year}`;
        },
        formatDateUTC(dateStr) {
            let date = new Date(dateStr);
            return `${date.getDate()}.${date.getMonth() + 1}.${date.getFullYear()}`;
        },
        giveSubscription () {
            let input = "";
            while (input === "" || isNaN(Number(input))) {
                input = prompt("Введите количество дней подписки (0 для удаления подписки): ");
                if (!input) return;
            }
            axios.post(config.backend + "admin/users/" + this.user.id + "/sub", {
                days: Number(input),
            }).then((response) => {
                this.user = deepParse(response.data);
                alert ("Успешно поставлено " + input + " дней подписки");
            })
        },
        async changeBonus () {
            if (this.bonuses < 0) return alert("Кол-во бонусов не может быть меньше нуля!")
            await axios.post(config.backend + "admin/users/" + this.$route.params.id + "/bonus", {
                bonus: this.bonuses
            }).then((response) => {
                alert("Успешно");
            }).catch((error) => {
                alert(whatError(error));
            });
        }
    },
    async mounted() {
        axios.defaults.withCredentials = true;

        await axios.get(config.backend + "admin/users/" + this.$route.params.id).then((response) => {
            this.user = response.data;
            this.bonuses = this.user.bonus;
            removeLoading();
        }).catch((error) => {
            if (error.response) {
                alert(error.message);
            }
        });
    }
}
</script>

<template>
    <adminnav>
        <!-- Профиль -->
        <section class="profile" aria-label="Информация о пользователе">
            <div class="avatar">
                <a :href="user.avatar" target="_blank"><img :src="user.avatar" alt="Аватар пользователя" loading="lazy" decoding="async"></a>
            </div>

            <div class="profile-info">
                <h2 class="user-name">{{ user.fullname }}</h2>
                <div class="ids">
                    <div class="id-row" title="Telegram">
                        <svg class="id-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="currentColor" d="M9.033 15.183L8.85 18.36c.322 0 .46-.138.626-.303l1.503-1.44 3.115 2.29c.571.316.976.15 1.132-.53l2.05-9.62c.209-.947-.342-1.316-.97-1.086L4.7 10.02c-.928.362-.915.883-.158 1.116l3.26 1.017 7.56-4.767c.356-.217.68-.097.413.12" />
                        </svg>
                        <span class="id-label">Telegram:</span>
                        <span class="id-value">
          <a class="id-link" target="_blank" rel="noopener noreferrer">{{ user.telegram_id }}</a>
        </span>
                    </div>

                    <div class="id-row" title="Внутренний ID">
                        <svg class="id-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="currentColor" d="M3 4h18v2H3V4zm0 7h18v2H3v-2zm0 7h18v2H3v-2z"/>
                        </svg>
                        <span class="id-label">ID:</span>
                        <span class="id-value">#{{user.id}}</span>
                    </div>
                </div>
            </div>
<!--            <div class="profile-crypto">-->
<!--                <div v-for="(crypt, key) in user.crypto">-->
<!--                    <img :src="user.currenciesData.find(x => x.coingeckoId === key).logo" alt=""> {{ user.currenciesData.find(x => x.coingeckoId === key).name }}: {{crypt.toFixed(2)}}-->
<!--                </div>-->
<!--            </div>-->
        </section>

        <div class="admin_user_sub">
            <div>Бонусов: </div>
            <input v-model="bonuses" type="number" min="0">
            <button @click="changeBonus">Изменить</button>
        </div>

        <!-- Курсы -->
        <section v-if="user.allergens" aria-label="Список пройденных курсов">
            <h3 class="section-title">Аллергены:</h3>
            <div>{{ JSON.parse(user.allergens) }}</div>
        </section>
    </adminnav>
</template>

<style scoped>
 .admin_user_sub {
     display: flex;
     flex-direction: row;
     gap: 20px;
 }
 .admin_user_sub>input {
     text-align: center;
     width: 100px;
 }
 .admin_user_sub>button {
     padding: 8px 16px;
     border-radius: 10px;
     background-color: #1E1E2F;
     transition: 0.2s;
 }
 .admin_user_sub>button:hover {
     background-color: #2a2a42;
 }
</style>
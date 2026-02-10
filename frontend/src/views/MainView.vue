<script>
import NavComponent from "@/components/NavComponent.vue";
import axios from 'axios';
import config from "@/config.json"
import {
    closeAllOverlays,
    closeOverlay,
    deepParse,
    endLoading,
    getPrevWithClass,
    levels,
    notify, sumCart,
    toLink
} from "@/utils.js";
import router from "@/router.js";
import HomeView from "@/views/HomeView.vue";
import ProfileView from "@/views/ProfileView.vue";
import CartView from "@/views/CartView.vue";
import ProductView from "@/views/ProductView.vue";
import AddressView from "@/views/AddressView.vue";
import HistoryView from "@/views/HistoryView.vue";
import BonusView from "@/views/BonusView.vue";
import OrderView from "@/views/OrderView.vue";
import AuthView from "@/views/AuthView.vue";
import AcceptView from "@/views/AcceptView.vue";
import AchievementsView from "@/views/AchievementsView.vue";

export default {
    name: "MainView",
    data () {
        return {
            queryHistory: [],
            isGoingBack: false,
            firstLoading: true,
            touch: false,

            dragStartY: 0,
            dragging: false,
            draggingOverlay: null,

            unlogged: false,
        }
    },
    components: {
        AchievementsView,
        AcceptView,
        AuthView,
        OrderView,
        BonusView,
        HistoryView,
        AddressView,
        ProductView,
        CartView,
        ProfileView,
        HomeView,
        NavComponent
    },
    async mounted () {
        this.theme = window.Telegram.WebApp.colorScheme;
        document.documentElement.classList.add(this.theme);

        document.addEventListener('gesturestart', function (e) {
            e.preventDefault();
        });
        document.addEventListener('gesturechange', function (e) {
            e.preventDefault();
        });
        document.addEventListener('gestureend', function (e) {
            e.preventDefault();
        });

        let lastTouchEnd = 0;
        document.addEventListener('touchend', function(event) {
            let now = new Date().getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);


        this.setHeaderColor();

        document.addEventListener('touchstart', function(event) {
            const activeElement = document.activeElement;
            if ((activeElement.tagName === 'INPUT' || activeElement.tagName === 'TEXTAREA')
                && !activeElement.contains(event.target)
                && event.target !== activeElement) {
                if (event.target.tagName !== 'INPUT' && event.target.tagName !== 'TEXTAREA' &&
                    !event.target.closest('.no-blur')) {
                    activeElement.blur();
                }
            }
        }, { passive: true });

        window.Telegram.WebApp.expand();
        window.Telegram.WebApp.disableVerticalSwipes();
        if (window.Telegram.WebApp.initDataUnsafe.start_param) {
            let origParams = decodeURIComponent(window.Telegram.WebApp.initDataUnsafe.start_param);
            const params = origParams.split("_");

            const sessionKey = 'tg_start_param';
            if (!sessionStorage.getItem(sessionKey)) {
                if (/^[0-9]+$/.test(params[1]) && Number(params[1]) >= 0)  {
                    if (params[0] === "user") toLink("user", params[1])
                }
                else this.$router.push({ query: { s: 'home' }});

                sessionStorage.setItem(sessionKey, "1")
            }
        }
        else if (!this.$route.query.s) this.$router.push({ query: { s: 'home' }});

        this.fetchData();

        window.Telegram.WebApp.BackButton.onClick(this.backByQuery);
        window.backByQueryFunction = this.backByQuery;

        window.addEventListener("touchstart", () => this.touch = true);
        // window.addEventListener("touchend", () => this.touch = false);

        this.hideFooter();
        this.handleDrag();
    },
    watch: {
        $route(to, from) {
            clearInterval(this.$store.state.interval);
            document.body.style.overflow = "";
        },
        '$route.query' (to, from) {
            this.setHeaderColor();
            document.body.style.overflow = "";

            const footer = document.querySelector('.nav');
            if (footer) {
                footer.style.display = '';
                footer.style.opacity = "1";
            }

            this.$nextTick(() => {
                this.hideFooter();
                this.handleDrag();
            })

            document.body.style.overflow = "";
            window.scrollTo({ top: 0, behavior: 'smooth' });
            if (this.isGoingBack === true) {
                this.isGoingBack = false;
                return;
            }
            if (from.s === undefined) return;

            if (to.needback === "1" || to.needback == undefined || to.needback == null) {
                this.queryHistory.push(from);
            }

            window.Telegram.WebApp.BackButton.offClick(window.backFunction);
            window.Telegram.WebApp.BackButton.onClick(window.backByQueryFunction);
            window.Telegram.WebApp.BackButton.show();
        }
    },
    methods: {
        async fetchData () {
            axios.post(config.backend + "auth/profile", {
                "initData": window.Telegram.WebApp.initData,
            }).then((response) => {
                let user = deepParse(JSON.stringify(response.data));
                if (user.cart == null) user.cart = [];
                user.cartSum = sumCart(user.cart, user.products);

                this.$store.dispatch("updateUser", user);
            }).catch((error) => {
                console.log(error);
                if (error.response && error.response.status === 423) {
                    return this.unlogged = true;
                } else {
                    // endLoading();
                }
            }).finally(() => {
                endLoading("loading")
            });
        },
        backByQuery() {
            if (this.queryHistory.length > 0) {
                this.isGoingBack = true;

                const prevQuery = this.queryHistory.pop();
                this.$router.push({ query: prevQuery });

                if (this.queryHistory.length === 0) window.Telegram.WebApp.BackButton.hide();
            } else {
                this.$router.push({ query: {s: 'home'} });
            }
        },
        hideFooter () {
            let footer = document.querySelector(".nav");
            if (footer) {
                document.querySelectorAll("input, textarea").forEach((el) => {
                    el.addEventListener("focus", () => {
                        if (this.touch) {
                            footer.style.opacity = "0";

                            let dialog = document.querySelector(".dialog")
                            if (dialog) dialog.style.height = "calc(100vh - 10px)";
                            document.querySelector(".nav").style.paddingBottom = "0px"
                        }
                    });
                    el.addEventListener("blur", () => {
                        footer.style.opacity = "1";

                        let dialog = document.querySelector(".dialog")
                        if (dialog) dialog.style.height = "";

                        document.querySelector(".nav").style.paddingBottom = "";
                    });
                })
            }
        },
        setHeaderColor () {
            const root = document.documentElement; // обычно переменные на :root
            const mainColor = getComputedStyle(root).getPropertyValue('--background').trim();
            window.Telegram.WebApp.setHeaderColor(mainColor);
        },
        handleDrag () {
            document.querySelectorAll('.overlay_closeArea').forEach(el => {
                let onmousedown = (ev) => {
                    this.dragStartY = ev.touches ? ev.touches[0].clientY : ev.clientY;
                    this.dragging = true;
                    this.draggingOverlay = el.closest(".overlay");

                    window.addEventListener('mousemove', this.onMoveDrag);
                    window.addEventListener('touchmove', this.onMoveDrag);
                    window.addEventListener('mouseup', this.onEndDrag);
                    window.addEventListener('touchend', this.onEndDrag);

                    document.documentElement.classList.add('user-unselect');
                }
                el.addEventListener('mousedown', onmousedown);
                el.addEventListener('touchstart', onmousedown);
            });
        },
        onMoveDrag(e) {
            if (this.dragging) {
                let el = this.draggingOverlay;
                let transformY = e.touches ? e.touches[0].clientY - this.dragStartY : e.clientY - this.dragStartY;
                if (transformY < 0) return;

                el.style.transition = 'none';
                el.style.transform = `translateY(${transformY}px)`;
            }
        },
        onEndDrag(e) {
            document.documentElement.classList.remove('user-unselect');
            if (!this.dragging) return;

            let el = this.draggingOverlay;
            el.style.transition = '';
            el.style.transform = 'translateY(0)';

            const endY = e.changedTouches ? e.changedTouches[0].clientY : e.clientY;
            const deltaY = endY - this.dragStartY;

            if (deltaY > 50) closeAllOverlays();

            window.removeEventListener('mousemove', this.onMoveDrag);
            window.removeEventListener('touchmove', this.onMoveDrag);
            window.removeEventListener('mouseup', this.onEndDrag);
            window.removeEventListener('touchend', this.onEndDrag);
            this.dragging = false;
            this.dragStartY = null;
        },
        async sendSettings () {
            if (this.selectedLevel !== 'student') this.faculty = "";
            let newUser =
                {...this.user, level: this.selectedLevel,
                    faculty: this.faculty === '' ? null : this.faculty,
                    isFirst: false};
            this.$store.commit('setUser', newUser);

            let data = {};
            data["initData"] = window.Telegram.WebApp.initData;
            data["level"] = this.selectedLevel;
            data["faculty"] = this.faculty === '' ? null : this.faculty;

            await axios.post(config.backend + 'auth/settings', data).then((response) => {
                notify('Успешно сохранено')
            }).catch((error) => {
                alert (error.response.data.message || 'Ошибка при отправке данных. Попробуйте позже.');
            });
        },
    },
    computed: {
        name () {
            return window.Telegram.WebApp.initDataUnsafe?.user?.first_name;
        },
        user() {
            return this.$store.state.user;
        },
    }
}
</script>

<template>
    <div class="loading" ref="loading"></div>
    <auth-view v-if="unlogged" @logged="unlogged = false"/>
    <div class="popup_notification_container"></div>
    <accept-view v-if="user.orders && user.orders.length > 0" />
    <nav-component>
        <home-view v-if="$route.query.s === 'home'" />
        <profile-view v-else-if="$route.query.s === 'profile'" />
        <cart-view v-else-if="$route.query.s === 'cart'" />
        <achievements-view v-else-if="$route.query.s === 'achievements'" />
        <product-view v-else-if="$route.query.s === 'product'" />
        <address-view v-else-if="$route.query.s === 'address'" />
        <history-view v-else-if="$route.query.s === 'history'" />
        <bonus-view v-else-if="$route.query.s === 'bonus'" />
        <order-view v-else-if="$route.query.s === 'order'" />
    </nav-component>
</template>

<style scoped>

</style>
import {createRouter, createWebHistory} from 'vue-router';
import MainView from "@/views/MainView.vue";
import AdminView from "@/views/admin/adminView.vue";
import AdminUsersView from "@/views/admin/adminUsersView.vue";
import AdminUserIndexView from "@/views/admin/adminUserIndexView.vue";
import AdminLoginView from "@/views/admin/adminLoginView.vue";
import adminMailingView from "@/views/admin/adminMailingView.vue";
import adminAdView from "@/views/admin/adminAdView.vue";
import adminAchievementsView from "@/views/admin/adminAchievementsView.vue";


const routes = [
    {
        path: "/",
        component: MainView,
        meta: {
            title: 'Доставка',
        }
    },

    {
        path: "/admin/login",
        component: AdminLoginView,
        meta: { title: 'CryptoCourses | Admin\'s Authorization' },
        name: 'adminlogin'
    },
    {
        path: "/admin",
        component: AdminView,
        meta: { title: 'CryptoCourses | Admin', h: 'Дашборд' },
        name: 'admin'
    },
    {
        path: "/admin/users",
        component: AdminUsersView,
        meta: { title: 'CryptoCourses | Users', h: 'Пользователи' },
        name: 'users'
    },
    {
        path: "/admin/users/:id",
        component: AdminUserIndexView,
        meta: { title: 'CryptoCourses | User', h: 'Пользователь' },
        name: 'user'
    },
    {
        path: "/admin/ads",
        component: adminAdView,
        meta: { title: 'CryptoCourses | Ads', h: 'Рекламы' },
        name: 'ads'
    },
    {
        path: "/admin/mailing",
        component: adminMailingView,
        meta: { title: 'CryptoCourses | Mailing', h: 'Рассылка' },
        name: 'mailing'
    },
    {
        path: "/admin/achievements",
        component: adminAchievementsView,
        meta: { title: 'CryptoCourses | Achievements', h: 'Достижения' },
        name: 'achievements'
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router;
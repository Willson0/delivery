<script>
import {addToCart, notify, removeFromCart, sumCart, toLink, whatError} from "@/utils.js";
import config from "@/config.json";
import axios from "axios";

export default {
    name: "CartView",
    data () {
        return {
            config: config,
        }
    },
    methods: {
        sumCart,
        removeFromCart,
        addToCart,
        toLink,
        close () {
            this.$emit('close');
        },
        getProduct (id) {
            return this.user?.products?.find(product => product.id === Number(id));
        },
        getProductsName (count) {
            const mod10 = count % 10;
            const mod100 = count % 100;

            if (mod10 === 1 && mod100 !== 11) return "товар";
            else if (mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20)) return "товара"
            else return "товаров";
        },
        async changeAddress (key) {
            let newUser = {...this.user};

            newUser.address = key;
            this.$store.dispatch("updateUser", newUser);

            await axios.post(config.backend + "auth/update", {
                address: key,
                initData: window.Telegram.WebApp.initData,
            }).then((response) => {})
                .catch((error) => {
                    notify(whatError(error),1);
                })
        },
        toOrder () {
            if (this.user.bonus < sumCart(this.user.cart, this.user.products, 1))
                return notify("Недостаточно бонусов для оформления заказа!", 1);
            toLink('order');
        }
    },
    computed: {
        user () {
            return this.$store.state.user;
        },
    },
    watch: {
        user () {
            if (!this.user.id) return;
            if (this.user.cart.length === 0) this.$emit('close');
        },
        'user.address' () {
            this.changeAddress(this.user.address);
        }
    }
}
</script>

<template>
    <div class="cart">
        <div class="cart_address" v-if="user.address != null">
            <div>{{ user.addresses[user.address].address }}</div>
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1.5 5.5L8 12L14.5 5.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <select v-model="user.address" name="" id="">
                <option v-for="(address, key) in user.addresses" :value="key">{{ address.address }}</option>
            </select>
        </div>
        <div class="cart_main">
            <div class="cart_main_close" @click="close()">Закрыть</div>
            <div class="cart_list_header"></div>
            <div class="cart_list">
                <div class="cart_list_item" v-for="(product) in user.cart">
                    <div class="cart_item_product">
                        <img :src="config.storage + getProduct(product.id).image" alt="">
                        <div class="cart_item_product_info">
                            <div class="cart_item_product_info_name">{{ getProduct(product.id).name }}</div>
                            <div class="cart_item_product_info_count">{{ product.count }} шт</div>
                        </div>
<!--                        <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">-->
<!--                            <path d="M4.05755 15.3754L13.2227 6.14804L10.0175 2.94287L0.807451 12.1529C0.621665 12.3387 0.51646 12.5902 0.514584 12.8529L0.499938 14.9033C0.495608 15.5095 1.02733 15.9801 1.62852 15.9021L3.47666 15.6624C3.69677 15.6339 3.90113 15.5329 4.05755 15.3754Z" fill="#8E8E93" stroke="#8E8E93" stroke-linecap="round"/>-->
<!--                            <path d="M15.2368 4.66603L11.7368 1.16603C11.7368 1.16603 13.7366 -0.333672 15.2368 1.16632C16.737 2.66632 15.2368 4.66603 15.2368 4.66603Z" fill="#8E8E93" stroke="#8E8E93" stroke-linecap="round"/>-->
<!--                        </svg>-->
                    </div>
                    <div class="cart_item_control">
                        <div class="cart_item_control_price">{{ getProduct(product.id).priceDiscount !== 0 ? getProduct(product.id).priceDiscount : getProduct(product.id).price }}
                            <span v-if="!product.isBonus">₽</span>
                            <svg v-else width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.71099 15.5325C2.33642 13.9419 2.3328 12.5196 2.95889 11.2123C3.45289 10.1822 4.09345 9.21358 4.78288 8.32418C5.53563 7.35356 5.59535 7.07228 4.78288 6.14128C3.74784 4.95672 1.27966 4.6794 0.152338 5.62031C-0.343468 3.42948 0.378527 2.26078 2.20794 2.10231C2.89375 2.04288 3.34251 1.81706 3.73698 1.14753C4.36669 0.075891 5.50668 0.075891 6.50372 0.0184462C9.89475 -0.175678 12.5167 1.14754 14.0403 4.69327C15.4409 7.95376 16.0652 11.3608 15.9946 14.9402C15.9892 15.2275 15.924 15.5147 15.8607 16C14.1561 14.7521 12.7266 13.2426 10.8574 12.589C10.7923 12.6524 10.7271 12.7138 10.6638 12.7771C10.9985 13.3892 11.3351 14.0033 11.7314 14.7303C10.177 14.9284 8.83437 14.5084 7.51524 13.9657C7.04115 13.7715 6.52182 13.5576 6.16896 13.1872C5.39087 12.3631 4.79554 12.6603 4.22555 13.4051C3.75146 14.0231 3.31717 14.6768 2.71099 15.5305V15.5325ZM5.53201 2.69458C5.48135 2.81938 5.43249 2.94417 5.38183 3.06699C5.70573 3.32252 6.02963 3.78009 6.35353 3.78207C6.80048 3.78207 7.26915 3.48098 7.6799 3.21555C7.80657 3.13235 7.8699 2.6926 7.78486 2.52225C7.70705 2.36378 7.37772 2.25681 7.18229 2.28851C6.62496 2.37963 6.0803 2.55196 5.53021 2.6926L5.53201 2.69458Z" fill="white"/>
                            </svg>
                        </div>
                        <div class="cart_item_control_count">
                            <div @click="removeFromCart(product.id, product.isBonus)" class="cart_item_control_count_svg">
                                <svg width="10" height="1" viewBox="0 0 10 1" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 0.5H10" stroke="white"/>
                                </svg>
                            </div>
                            <div class="cart_item_control_count_number">{{ product.count }}</div>
                            <div @click="addToCart(product.id, 0, product.isBonus)" class="cart_item_control_count_svg">
                                <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 5H10" stroke="white"/>
                                    <path d="M5 0L5 10" stroke="white"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cart_promo">
                    <input type="text" placeholder="Ввести промокод">
                </div>
                <div class="cart_summa">
                    <div v-if="user.cart">
                        <div>{{ user.cart?.reduce((acc, val) => acc + Number(val.count), 0) }} {{ getProductsName(user.cart?.reduce((acc, val) => acc + Number(val.count), 0)) }}</div>
                        <div>
                            <template v-if="!(sumCart(user.cart, user.products, 1) > 0)">{{ user.cartSum }} ₽</template>
                            <div v-else class="cart_summa_bonuses">
                                <div>{{ user.cartSum }} ₽ + {{ sumCart(user.cart, user.products, 1) }}</div>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2.71099 15.5325C2.33642 13.9419 2.3328 12.5196 2.95889 11.2123C3.45289 10.1822 4.09345 9.21358 4.78288 8.32418C5.53563 7.35356 5.59535 7.07228 4.78288 6.14128C3.74784 4.95672 1.27966 4.6794 0.152338 5.62031C-0.343468 3.42948 0.378527 2.26078 2.20794 2.10231C2.89375 2.04288 3.34251 1.81706 3.73698 1.14753C4.36669 0.075891 5.50668 0.075891 6.50372 0.0184462C9.89475 -0.175678 12.5167 1.14754 14.0403 4.69327C15.4409 7.95376 16.0652 11.3608 15.9946 14.9402C15.9892 15.2275 15.924 15.5147 15.8607 16C14.1561 14.7521 12.7266 13.2426 10.8574 12.589C10.7923 12.6524 10.7271 12.7138 10.6638 12.7771C10.9985 13.3892 11.3351 14.0033 11.7314 14.7303C10.177 14.9284 8.83437 14.5084 7.51524 13.9657C7.04115 13.7715 6.52182 13.5576 6.16896 13.1872C5.39087 12.3631 4.79554 12.6603 4.22555 13.4051C3.75146 14.0231 3.31717 14.6768 2.71099 15.5305V15.5325ZM5.53201 2.69458C5.48135 2.81938 5.43249 2.94417 5.38183 3.06699C5.70573 3.32252 6.02963 3.78009 6.35353 3.78207C6.80048 3.78207 7.26915 3.48098 7.6799 3.21555C7.80657 3.13235 7.8699 2.6926 7.78486 2.52225C7.70705 2.36378 7.37772 2.25681 7.18229 2.28851C6.62496 2.37963 6.0803 2.55196 5.53021 2.6926L5.53201 2.69458Z" fill="white"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div>Доставка</div>
                        <div>200 ₽</div>
                    </div>
                    <div>
                        <div>Бонусы</div>
                        <div class="cart_summa_bonuses" v-if="user.settings">
                            <div>+{{ Math.floor(user.cartSum * (Number(user.settings?.find(a => a.key === "bonusPercent").value) / 100)) }}</div>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.71099 15.5325C2.33642 13.9419 2.3328 12.5196 2.95889 11.2123C3.45289 10.1822 4.09345 9.21358 4.78288 8.32418C5.53563 7.35356 5.59535 7.07228 4.78288 6.14128C3.74784 4.95672 1.27966 4.6794 0.152338 5.62031C-0.343468 3.42948 0.378527 2.26078 2.20794 2.10231C2.89375 2.04288 3.34251 1.81706 3.73698 1.14753C4.36669 0.075891 5.50668 0.075891 6.50372 0.0184462C9.89475 -0.175678 12.5167 1.14754 14.0403 4.69327C15.4409 7.95376 16.0652 11.3608 15.9946 14.9402C15.9892 15.2275 15.924 15.5147 15.8607 16C14.1561 14.7521 12.7266 13.2426 10.8574 12.589C10.7923 12.6524 10.7271 12.7138 10.6638 12.7771C10.9985 13.3892 11.3351 14.0033 11.7314 14.7303C10.177 14.9284 8.83437 14.5084 7.51524 13.9657C7.04115 13.7715 6.52182 13.5576 6.16896 13.1872C5.39087 12.3631 4.79554 12.6603 4.22555 13.4051C3.75146 14.0231 3.31717 14.6768 2.71099 15.5305V15.5325ZM5.53201 2.69458C5.48135 2.81938 5.43249 2.94417 5.38183 3.06699C5.70573 3.32252 6.02963 3.78009 6.35353 3.78207C6.80048 3.78207 7.26915 3.48098 7.6799 3.21555C7.80657 3.13235 7.8699 2.6926 7.78486 2.52225C7.70705 2.36378 7.37772 2.25681 7.18229 2.28851C6.62496 2.37963 6.0803 2.55196 5.53021 2.6926L5.53201 2.69458Z" fill="white"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div @click="toOrder" v-if="user.cart?.length" class="cart_footer">
            <button>Оформить заказ на {{ (user.cartSum+200).toString().split('').reverse().join('').match(/.{1,3}/g)
                .join(' ').split('').reverse().join('').trim() }} ₽</button>
        </div>
    </div>
</template>

<style scoped>

</style>
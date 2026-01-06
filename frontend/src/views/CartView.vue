<script>
export default {
    name: "CartView",
    methods: {
        close () {
            window.backByQueryFunction();
        }
    }
}
</script>

<template>
    <div class="cart">
        <div class="cart_address">
            <div>Самара, Садовая улица, 230</div>
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1.5 5.5L8 12L14.5 5.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="cart_main">
            <div class="cart_main_close" @click="close()">Закрыть</div>
            <div class="cart_list">
                <div class="cart_list_item" v-for="el in 4">
                    <div class="cart_item_product">
                        <img src="/pizza.png" alt="">
                        <div class="cart_item_product_info">
                            <div class="cart_item_product_info_name">Пицца 4 сыра</div>
                            <div class="cart_item_product_info_count">1 шт</div>
                        </div>
                        <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4.05755 15.3754L13.2227 6.14804L10.0175 2.94287L0.807451 12.1529C0.621665 12.3387 0.51646 12.5902 0.514584 12.8529L0.499938 14.9033C0.495608 15.5095 1.02733 15.9801 1.62852 15.9021L3.47666 15.6624C3.69677 15.6339 3.90113 15.5329 4.05755 15.3754Z" fill="#8E8E93" stroke="#8E8E93" stroke-linecap="round"/>
                            <path d="M15.2368 4.66603L11.7368 1.16603C11.7368 1.16603 13.7366 -0.333672 15.2368 1.16632C16.737 2.66632 15.2368 4.66603 15.2368 4.66603Z" fill="#8E8E93" stroke="#8E8E93" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="cart_item_control">
                        <div class="cart_item_control_price">552 ₽</div>
                        <div class="cart_item_control_count">
                            <svg width="10" height="1" viewBox="0 0 10 1" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 0.5H10" stroke="white"/>
                            </svg>
                            <div>1</div>
                            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 5H10" stroke="white"/>
                                <path d="M5 0L5 10" stroke="white"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="cart_promo">
                    <input type="text" placeholder="Ввести промокод">
                </div>
                <div class="cart_summa">
                    <div>
                        <div>2 товара</div>
                        <div>1000 ₽</div>
                    </div>
                    <div>
                        <div>Доставка</div>
                        <div>Бесплатно</div>
                    </div>
                    <div>
                        <div>Бонусы</div>
                        <div class="cart_summa_bonuses">
                            <div>+38</div>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.71099 15.5325C2.33642 13.9419 2.3328 12.5196 2.95889 11.2123C3.45289 10.1822 4.09345 9.21358 4.78288 8.32418C5.53563 7.35356 5.59535 7.07228 4.78288 6.14128C3.74784 4.95672 1.27966 4.6794 0.152338 5.62031C-0.343468 3.42948 0.378527 2.26078 2.20794 2.10231C2.89375 2.04288 3.34251 1.81706 3.73698 1.14753C4.36669 0.075891 5.50668 0.075891 6.50372 0.0184462C9.89475 -0.175678 12.5167 1.14754 14.0403 4.69327C15.4409 7.95376 16.0652 11.3608 15.9946 14.9402C15.9892 15.2275 15.924 15.5147 15.8607 16C14.1561 14.7521 12.7266 13.2426 10.8574 12.589C10.7923 12.6524 10.7271 12.7138 10.6638 12.7771C10.9985 13.3892 11.3351 14.0033 11.7314 14.7303C10.177 14.9284 8.83437 14.5084 7.51524 13.9657C7.04115 13.7715 6.52182 13.5576 6.16896 13.1872C5.39087 12.3631 4.79554 12.6603 4.22555 13.4051C3.75146 14.0231 3.31717 14.6768 2.71099 15.5305V15.5325ZM5.53201 2.69458C5.48135 2.81938 5.43249 2.94417 5.38183 3.06699C5.70573 3.32252 6.02963 3.78009 6.35353 3.78207C6.80048 3.78207 7.26915 3.48098 7.6799 3.21555C7.80657 3.13235 7.8699 2.6926 7.78486 2.52225C7.70705 2.36378 7.37772 2.25681 7.18229 2.28851C6.62496 2.37963 6.0803 2.55196 5.53021 2.6926L5.53201 2.69458Z" fill="white"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="cart_footer">
            <button>Оформить заказ на 1 000 ₽</button>
        </div>
    </div>
</template>

<style scoped>

</style>
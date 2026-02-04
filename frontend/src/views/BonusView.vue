<script>
import ProductComponent from "@/components/ProductComponent.vue";

export default {
    name: "BonusView",
    components: {ProductComponent},
    computed: {
        user () {
            return this.$store.state.user;
        }
    },
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
    methods: {
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
    }
}
</script>

<template>
    <div class="bonus">
        <div v-for="section in user.sections">
            <div class="bonus_header">
                <div class="bonus_title">{{ section.name }}</div>
                <div class="bonus_points">
                    <div>{{ user.bonus }}</div>
                    <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.88043 15.5325C2.48245 13.9419 2.4786 12.5196 3.14383 11.2123C3.6687 10.1822 4.3493 9.21358 5.08182 8.32418C5.88162 7.35356 5.94507 7.07228 5.08182 6.14128C3.98208 4.95672 1.35964 4.6794 0.16186 5.62031C-0.364935 3.42948 0.402186 2.26078 2.34594 2.10231C3.07461 2.04288 3.55142 1.81706 3.97055 1.14753C4.63962 0.075891 5.85086 0.075891 6.91022 0.0184462C10.5132 -0.175678 13.299 1.14754 14.9179 4.69327C16.406 7.95376 17.0693 11.3608 16.9943 14.9402C16.9885 15.2275 16.9193 15.5147 16.852 16C15.0409 14.7521 13.5221 13.2426 11.536 12.589C11.4668 12.6524 11.3976 12.7138 11.3303 12.7771C11.686 13.3892 12.0436 14.0033 12.4646 14.7303C10.8131 14.9284 9.38654 14.5084 7.98495 13.9657C7.48123 13.7715 6.92944 13.5576 6.55453 13.1872C5.72781 12.3631 5.09527 12.6603 4.48965 13.4051C3.98593 14.0231 3.5245 14.6768 2.88043 15.5305V15.5325ZM5.87778 2.69458C5.82394 2.81938 5.77203 2.94417 5.7182 3.06699C6.06235 3.32252 6.40649 3.78009 6.75064 3.78207C7.22552 3.78207 7.72348 3.48098 8.15991 3.21555C8.29449 3.13235 8.36179 2.6926 8.27142 2.52225C8.18875 2.36378 7.83884 2.25681 7.63119 2.28851C7.03903 2.37963 6.46033 2.55196 5.87585 2.6926L5.87778 2.69458Z" fill="white"/>
                    </svg>
                </div>
            </div>
            <div class="bonus_slider slider" @mousedown.prevent="mousedown">
                <product-component :clickable="!isDragging" :is-bonus="true" :product="el" v-for="el in user.products?.filter(pr => pr.sectionId === section.id)?.sort((a,b) => b.rating - a.rating)" />
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
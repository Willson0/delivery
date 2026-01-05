<script>
export default {
    async mounted () {
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
    }
}
</script>

<template>
    <div class="notification_container"></div>
    <router-view />
</template>

<style scoped>
</style>

<template>
    <transition name="overlay-fade">
        <div v-if="visible" class="welcome-overlay">

            <!-- CONTENEDOR ORBITAL -->
            <div class="orbit">
                <DotLottieVue
                    class="rocket"
                    autoplay
                    loop
                    src="/img/rocket.json"
                />
            </div>

            <!-- CARD -->
            <div class="card">
                <h2 class="welcome">{{ welcome }}</h2>
                <p class="quote">“{{ quote }}”</p>

                <button class="btn-accept" @click="close">
                    Aceptar
                </button>
            </div>

        </div>
    </transition>
</template>




<script>
import { DotLottieVue } from '@lottiefiles/dotlottie-vue';
import { getDailyMessage } from '../utils/motivationalMessages';

export default {
    name: 'LoginWelcome',
    components: {
        DotLottieVue
    },
    data() {
        return {
            visible: false,
            welcome: '',
            quote: ''
        };
    },
    mounted() {
        if (window.LOGIN_SUCCESS) {
            const message = getDailyMessage();
            this.welcome = message.welcome;
            this.quote = message.quote;
            this.visible = true;
        }
    },
    methods: {
        close() {
            this.visible = false;
        }
    }
};
</script>


<style scoped>
/* Overlay */
.welcome-overlay {
    position: fixed;
    inset: 0;
    background: linear-gradient(135deg,
            rgba(22, 113, 96, 0.92),
            rgba(19, 109, 40, 0.92));
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

/* Card */
.card {
    background: #ffffff;
    padding: 36px 40px;
    border-radius: 20px;
    width: 90%;
    max-width: 420px;
    text-align: center;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25);
    animation: cardIn 0.5s ease;
}

/* Loader */
.spinner {
    width: 42px;
    height: 42px;
    margin: 0 auto 20px;
    border: 4px solid #e5f3ee;
    border-top-color: #167160;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* Textos */
.welcome {
    font-size: 1.5rem;
    font-weight: 600;
    color: #167160;
    margin-bottom: 12px;
}

.quote {
    font-size: 1rem;
    color: #555;
    line-height: 1.5;
}

.btn-accept {
    margin-top: 24px;
    padding: 10px 28px;
    border-radius: 999px;
    border: none;
    background: linear-gradient(to right,
            #167160,
            #2abb00);
    color: white;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.25s ease;
    box-shadow: 0 8px 20px rgba(22, 113, 96, 0.35);
}

.btn-accept:hover {
    transform: translateY(-1px);
    box-shadow: 0 12px 28px rgba(22, 113, 96, 0.45);
}

.btn-accept:active {
    transform: scale(0.97);
}

/* Animaciones */
@keyframes cardIn {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.96);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Transition Vue */
.overlay-fade-enter-active,
.overlay-fade-leave-active {
    transition: opacity 0.4s ease;
}

.overlay-fade-enter-from,
.overlay-fade-leave-to {
    opacity: 0;
}

.orbit {
    position: fixed;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    animation: orbitRotate 10s linear infinite;
    z-index: 9998;
}


.rocket {
    width: 160px;
    height: 160px;
    transform: translateX(300px);
}

@keyframes orbitRotate {
    from {
        transform: rotate(360deg);
    }
    to {
        transform: rotate(0deg);
    }
}

</style>

/**
 * Mensajes y frases motivacionales para inicio de sesión
 */

/* ==============================
   MENSAJES DE BIENVENIDA
================================ */
const welcomeMessages = [
    '¡Bienvenido de nuevo!',
    'Nos alegra verte otra vez.',
    'Todo está listo para comenzar.',
    'Buen día, sigamos avanzando.',
    'Qué gusto tenerte de vuelta.',
];

/* ==============================
   FRASES MOTIVACIONALES
================================ */
const motivationalQuotes = [
    'La constancia convierte pequeños esfuerzos en grandes resultados.',
    'Cada paso cuenta, incluso los más pequeños.',
    'El progreso diario es la clave del éxito.',
    'Hoy es una nueva oportunidad para mejorar.',
    'La disciplina supera a la motivación cuando esta falla.',
    'Las grandes metas se alcanzan con acciones consistentes.',
    'Avanzar, incluso lento, sigue siendo avanzar.',
    'El trabajo bien hecho siempre deja huella.',
];

/* ==============================
   HELPERS
================================ */

function pick(arr, seed = null) {
    if (seed === null) {
        return arr[Math.floor(Math.random() * arr.length)]
    }

    let hash = 0
    for (let i = 0; i < seed.length; i++) {
        hash += seed.charCodeAt(i)
    }

    return arr[hash % arr.length]
}

/**
 * Mensaje diario (mismo durante todo el día)
 */
export function getDailyMessage() {
    const today = new Date().toISOString().slice(0, 10)

    return {
        welcome: pick(welcomeMessages, today),
        quote: pick(motivationalQuotes, today + 'quote')
    }
}

/**
 * Mensaje aleatorio (cada login)
 */
export function getRandomMessage() {
    return {
        welcome: pick(welcomeMessages),
        quote: pick(motivationalQuotes)
    }
}

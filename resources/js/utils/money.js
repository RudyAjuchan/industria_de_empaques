export function parseMoney(value) {
    if (value === null || value === undefined || value === '') {
        return null
    }

    const normalized = String(value).replace(/[^\d.-]/g, '')
    const number = Number(normalized)

    return Number.isFinite(number) ? number : null
}

export function formatQuetzales(value) {
    const number = parseMoney(value) ?? 0

    return `Q ${new Intl.NumberFormat('es-GT', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(number)}`
}

export function useFormatters() {
    const formatPrice = (price) => {
        return new Intl.NumberFormat('ru-RU', {
            style: 'currency',
            currency: 'RUB',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(price)
    }

    const formatRelativeDate = (dateString) => {
        if (!dateString) return ''

        const date = new Date(dateString)
        const now = new Date()
        const diffDays = Math.floor((now - date) / (1000 * 60 * 60 * 24))

        if (diffDays === 0) return 'сегодня'
        if (diffDays === 1) return 'вчера'
        if (diffDays < 7) return `${diffDays} дня назад`
        if (diffDays < 30) return `${Math.floor(diffDays / 7)} недели назад`
        return date.toLocaleDateString('ru-RU')
    }

    const truncateText = (text, maxLength = 100) => {
        if (!text) return ''
        if (text.length <= maxLength) return text
        return text.slice(0, maxLength).trim() + '...'
    }

    return {
        formatPrice,
        formatRelativeDate,
        truncateText
    }
}

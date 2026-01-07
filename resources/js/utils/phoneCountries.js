import { getCountries, getCountryCallingCode } from 'libphonenumber-js'
import countries from 'i18n-iso-countries'
import es from 'i18n-iso-countries/langs/es.json'

countries.registerLocale(es)

export function getPhoneCountries() {
    return getCountries().map(iso => ({
        iso,
        name: countries.getName(iso, 'es') || iso,
        code: `+${getCountryCallingCode(iso)}`,
        flag: `https://flagcdn.com/w20/${iso.toLowerCase()}.png`
    }))
}

import en from '@lang/en.json';
import languages from '@lang/languages.json';
import { createI18n } from 'vue-i18n';

const messages = { en };
export const LANGUAGES = languages.available;
export const SUPPORT_LOCALES = languages.available.map(l => l.value).filter(l => l != 'en');
export const RTL_LOCALES = languages.available.filter(l => l.rtl).map(l => l.value);

export const isRtlLocale = locale => RTL_LOCALES.includes(locale);

export const getDocumentDirection = locale => (isRtlLocale(locale) ? 'rtl' : 'ltr');

const i18n = createI18n({
  messages,
  legacy: false,
  missingWarn: false,
  mode: 'composition',
  fallbackWarn: false,
  fallbackLocale: 'en',
  warnHtmlMessage: false,
  locale: typeof window === 'undefined' ? 'en' : window.Locale || 'en',
  missing: async (locale, key) => {
    // console.log('"' + key + '" missing for ' + locale + ' locale.');
    console.log('Add to ' + locale + '.json => "' + key + '": "' + key + '",');
  },
});

export default i18n;

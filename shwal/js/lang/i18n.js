import Vue from 'vue'
import VueI18n from 'vue-i18n'
Vue.use(VueI18n)

const ua_messages = {
    "change_info": "Перевірьте дані",
    "change_send": "Надсилання",
    "open_change_error": "Необхідно відкрити зміну",

    "table_add_send": "Надсилання ...",

    "login_phone": "Номер телефону",
    "login_phone_plac": "Введіть ваш номер телефону з 0",

    "login_code": "Введіть код",
    "login_enter": "Увійти",
    "notUserLogin": "Користувач не знайдений",
    "print": "Друк",
    "select_roles": "Виберіть роль"
};

const ru_messages = {
    "change_info": "Проверьте данные",
    "change_send": "Отправка",
    "open_change_error": "Необходимо открыть смену",

    "table_add_send": "Идет отправка ...",

    "login_phone": "Номер телефона",
    "login_phone_plac": "Введите ваш номер телефона с 0",
    "login_code": "Введите код",
    "login_enter": "Войти",
    "notUserLogin": "Пользователь не найден",
    "print": "Печать",
    "select_roles": "Выберите роль"
};
const i18n = new VueI18n({
    locale: 'ua',
    messages: {}
});
export async function loadMessages(locale) {
    if (Object.keys(i18n.getLocaleMessage(locale)).length === 0) {
        if (locale == 'ua') {
            i18n.setLocaleMessage(locale, ua_messages)
        } else {
            i18n.setLocaleMessage(locale, ru_messages)
        }
    }
    if (i18n.locale !== locale) {
        i18n.locale = locale
    }
};
(async function () {
    await loadMessages(LanguneThisJs)
})()

export default i18n
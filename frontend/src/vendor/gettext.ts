import {createGettext, Language} from "vue3-gettext";
import {App} from "vue";
import {usePheme} from "~/vendor/pheme.ts";

let gettext;

export function useTranslate(): Language {
    return gettext;
}

export async function installTranslate(vueApp: App): Promise<void> {
    const {locale} = usePheme();

    const translations = import.meta.glob('../../../translations/**/translations.json', {as: 'json'});
    const localePath = '../../../translations/' + locale + '.UTF-8/translations.json';

    gettext = createGettext({
        defaultLanguage: locale,
        translations: (localePath in translations) ?
            await translations[localePath]()
            : {},
        silent: true
    });

    vueApp.use(gettext);
}

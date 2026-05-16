import initApp from "~/layout";
import {h} from "vue";
import {createRouter, createWebHistory} from "vue-router";
import StationsLayout from "~/components/Stations/StationsLayout.vue";
import useStationsRoutes from "~/components/Stations/routes";
import {usePheme} from "~/vendor/pheme";
import {installRouter} from "~/vendor/router";

initApp({
    render() {
        return h(StationsLayout);
    }
}, (vueApp) => {
    const routes = useStationsRoutes();
    const {componentProps} = usePheme();

    installRouter(
        createRouter({
            history: createWebHistory(componentProps.baseUrl),
            routes
        }),
        vueApp
    );
});



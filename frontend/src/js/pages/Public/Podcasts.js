import initApp from "~/layout";
import {h} from "vue";
import {createRouter, createWebHistory} from "vue-router";
import {usePheme} from "~/vendor/pheme";
import {installRouter} from "~/vendor/router";
import PodcastsLayout from "~/components/Public/Podcasts/PodcastsLayout.vue";
import usePodcastRoutes from "~/components/Public/Podcasts/routes";

initApp({
    render() {
        return h(PodcastsLayout);
    }
}, async (vueApp) => {
    const routes = usePodcastRoutes();
    const {componentProps} = usePheme();

    installRouter(
        createRouter({
            history: createWebHistory(componentProps.baseUrl),
            routes
        }),
        vueApp
    );
});

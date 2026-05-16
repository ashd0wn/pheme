import {usePheme} from "~/vendor/pheme";
import {Component, h} from "vue";
import MinimalLayoutComponent from "~/components/MinimalLayout.vue";

export default function useMinimalLayout(component: Component) {
    return {
        setup() {
            const {componentProps} = usePheme();

            return {
                componentProps
            }
        },
        render() {
            return h(
                MinimalLayoutComponent,
                {},
                {
                    default: () => h(component, this.componentProps)
                }
            );
        }
    }
}

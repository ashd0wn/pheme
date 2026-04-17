<template>
    <panel-layout v-bind="panelProps">
        <template
            v-if="!isHome"
            #sidebar
        >
            <sidebar v-bind="sidebarProps" />
        </template>
        <template #default>
            <router-view />
        </template>
    </panel-layout>
</template>

<script setup lang="ts">
import PanelLayout from "~/components/PanelLayout.vue";
import {usePheme} from "~/vendor/pheme.ts";
import {useRoute} from "vue-router";
import {ref, watch} from "vue";
import Sidebar from "~/components/Admin/Sidebar.vue";

const {panelProps, sidebarProps} = usePheme();

const isHome = ref(true);
const route = useRoute();

watch(route, (newRoute) => {
    isHome.value = newRoute.name === 'admin:index';
});
</script>

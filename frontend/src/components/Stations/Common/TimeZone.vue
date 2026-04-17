<template>
    {{
        $gettext(
            'This station\'s time zone is currently %{tz}.',
            {tz: timezone}
        )
    }}
</template>

<script setup lang="ts">
import {usePhemeStation} from "~/vendor/pheme";

const {timezone} = usePhemeStation();
</script>

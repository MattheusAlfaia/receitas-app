<template>
  <RouterView />
  <UiToast />
  <ModalDetalhesReceita />
</template>

<script setup lang="ts">
import { RouterView } from 'vue-router'
import UiToast from './components/ui/UiToast.vue'
import ModalDetalhesReceita from './components/ModalDetalhesReceita.vue'
</script>

<template>
    <div class="from-group">
        <label :for="id" class="form-label">{{ titulo }}</label>
        <slot></slot>
        <small :id="idHelp" class="form-text text-muted">{{ textoAjuda }}</small>
    </div>
</template>

<script>
    export default {
        props: [
            'id',
            'titulo',
            'idHelp',
            'textoAjuda'
        ]
    }
</script>

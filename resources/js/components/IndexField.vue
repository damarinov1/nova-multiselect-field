<template>
    <span>{{ value }}</span>
</template>

<script>
    export default {
        props: ['resourceName', 'field'],

        computed: {
            value() {
                if (!this.field.value) return '-';

                let valuesArray = JSON.parse(this.field.value);

                if (!Array.isArray(valuesArray)) {
                    valuesArray = [
                        valuesArray
                    ].filter(val => !!val)
                }

                if (!Array.isArray(valuesArray)) return '-';

                const values = valuesArray
                    .map(val => this.field.options.find(opt => String(opt.value) === String(val)))
                    .filter(val => !!val)
                    .map(val => val.label);

                const joinedValues = values.join(', ');
                if (joinedValues.length <= 40) return joinedValues;

                return `${values.length} items selected`;
            },
        },
    };
</script>

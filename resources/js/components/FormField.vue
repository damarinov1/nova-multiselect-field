<template>
    <default-field :field="field" :errors="errors">
        <template slot="field">
            <multiselect
                @input="handleChange"
                @open="() => repositionDropdown(true)"
                track-by="value"
                label="label"
                ref="multiselect"
                :value="selected"
                :options="options"
                :class="classes"
                :placeholder="field.placeholder || field.name"
                :close-on-select="typeof field.multiple !== 'undefined' ? !field.multiple : false"
                :clear-on-select="false"
                :multiple="typeof field.multiple !== 'undefined' ? field.multiple : true"
                :max="field.max || null"
                :allowEmpty="field.allowEmpty"
                :optionsLimit="field.optionsLimit || 1000"
                :searchable="field.searchable"
                :loading="isLoading"
                :hide-selected="field.searchable"
                :show-no-results="!field.searchable"
                :internal-search="!field.searchable"
                :option-height="field.template === 'posts' || field.template === 'two-rows' ? 60 : 40"
                :show-labels="false"
                @search-change="getOptions"
            >
                <template slot="noOptions" v-if="field.noOptions">{{ field.noOptions }}</template>
                <template slot="noResults" v-if="field.noResults">{{ field.noResults }}</template>

                <template v-if="field.template === 'posts'" slot="option" slot-scope="props">
                    <div style="width: 100%; ">
                        <div style="max-width: 100%; overflow: hidden; text-overflow: ellipsis" :title="props.option.label">
                            <span>{{ props.option.label }}</span>
                        </div>
                        <div style="opacity: 0.75; margin-top: 4px">
                            <span v-if="props.option.publish_status">
                                {{ props.option.type }}
                                ({{ props.option.publish_status }})
                            </span>
                            <span v-else>
                                {{ props.option.type }}
                            </span>
                        </div>
                    </div>
                </template>

                <template v-if="field.template === 'two-rows'" slot="option" slot-scope="props">
                    <div style="width: 100%; ">
                        <div style="max-width: 100%; overflow: hidden; text-overflow: ellipsis" :title="props.option.label">
                            <span>{{ props.option.label }}</span>
                        </div>
                        <div style="opacity: 0.75; margin-top: 4px; max-width: 100%; overflow: hidden; text-overflow: ellipsis">
                            <span>
                                {{ props.option.subtitle }}
                            </span>
                        </div>
                    </div>
                </template>
            </multiselect>
        </template>
    </default-field>
</template>

<script>
import {FormField, HandlesValidationErrors} from 'laravel-nova';
import Multiselect from 'vue-multiselect';

export default {
    components: {Multiselect},

    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field'],

    data: () => ({
        isLoading: false,
        currentOptions: null,
    }),

    mounted() {
        window.addEventListener('scroll', this.repositionDropdown);
    },

    destroyed() {
        window.removeEventListener('scroll', this.repositionDropdown);
    },

    computed: {
        selected() {
            console.log(this.value)
            return this.value || [];
        },
        options: {
            set(options) {
                this.currentOptions = options
            },
            get() {
                return this.currentOptions || this.field.options || [];
            },
        },
        classes() {
            let classes = this.errorClasses

            if (this.field.template === 'posts' || this.field.template === 'two-rows') {
                classes.push('posts-multiselect')
            }

            return classes
        }
    },

    methods: {
        getOptions(query) {
            if (!this.field.searchable) {
                return;
            }

            if (!query) {
                this.options = [];

                return;
            }

            this.isLoading = true;

            Nova.request().get(this.field.searchUrl, {
                params: {
                    name: this.field.attribute,
                    query: query
                }
            }).then((response) => {
                this.options = typeof response.data === 'object' ? Object.values(response.data) : response.data;
            }).catch(() => {
                this.options = [];
            }).finally(() => {
                this.isLoading = false;
            });
        },

        setInitialValue() {
            if (this.field.value) {
                const valuesArray = JSON.parse(this.field.value);

                if (!Array.isArray(valuesArray)) {
                    this.value = this.field.options.find(opt => String(opt.value) === String(valuesArray));
                    return;
                }

                this.value = valuesArray
                    .map(val => this.field.options.find(opt => String(opt.value) === String(val)))
                    .filter(val => !!val);
            } else {
                this.value = [];
            }
        },

        fill(formData) {
            let value;
            let field = this.field;

            if (this.value && this.value.length) {
                value = JSON.stringify(this.value.map(v => v.value));
            }
            else if (typeof field.multiple !== 'undefined' ? field.multiple : true) {
                value = this.field.nullable ? '' : JSON.stringify([]);
            }
            else {
                value = this.value ? this.value.value : ''
            }

            formData.append(this.field.attribute, value);
        },

        handleChange(value) {
            this.value = value;
            this.$nextTick(() => this.repositionDropdown());
        },

        repositionDropdown(onOpen = false) {
            const ms = this.$refs.multiselect;
            const el = this.$el.children[1].children[0];

            const handlePositioning = () => {
                const {top, height, bottom} = el.getBoundingClientRect();
                if (onOpen) ms.$refs.list.scrollTop = 0;

                const fromBottom = (window.innerHeight || document.documentElement.clientHeight) - bottom;

                ms.$refs.list.style.position = 'fixed';
                ms.$refs.list.style.width = `${el.clientWidth}px`;

                if (fromBottom < 300) {
                    ms.$refs.list.style.top = 'auto';
                    ms.$refs.list.style.bottom = `${fromBottom + height}px`;
                } else {
                    ms.$refs.list.style.bottom = 'auto';
                    ms.$refs.list.style.top = `${top + height}px`;
                }
            };

            if (onOpen) this.$nextTick(handlePositioning);
            else handlePositioning();
        },
    },
};
</script>

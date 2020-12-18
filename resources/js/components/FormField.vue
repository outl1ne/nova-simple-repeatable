<template>
  <default-field :field="field" :errors="errors" :show-help-text="showHelpText">
    <template slot="field">
      <div class="flex flex-col">
        <div class="flex">
          <component
            v-for="(repField, i) in field.repeatableFields"
            :key="i"
            :is="`form-${repField.component}`"
            :field="repField"
          />
        </div>
      </div>
    </template>
  </default-field>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova';

export default {
  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  data() {
    return {
      vatChecked: this.field.storedWithVat,
    };
  },

  methods: {
    vatChanged(e) {
      this.vatChecked = e.target.checked;

      if (this.field.updatesWithCheckbox) {
        this.value = this.getValueWithAdjustedVAT(this.value);
      }
    },

    fill(formData) {
      const valueToSend = this.getValueWithAdjustedVAT(this.value);
      formData.append(this.field.attribute, valueToSend);
    },

    getValueWithAdjustedVAT(value) {
      const precision = this.field.step ? this.field.step.split('.')[1].length : 2;
      if (!value || isNaN(value)) return void 0;

      if (!this.field.vat || isNaN(this.field.vat)) return value;

      // Same as original, no need to do anything
      if (this.vatChecked && this.field.storedWithVat) return value;
      if (!this.vatChecked && !this.field.storedWithVat) return value;

      let newValue = value;

      // If VAT is checked, it means the original should be without VAT
      if (this.vatChecked) {
        newValue = value / (1 + this.field.vat / 100);
      } else {
        newValue = value * (1 + this.field.vat / 100);
      }

      newValue *= Math.pow(10, precision);
      newValue = Math.round(newValue);
      newValue /= Math.pow(10, precision);

      return newValue;
    },
  },

  computed: {
    defaultAttributes() {
      return {
        type: 'number',
        min: this.field.min,
        max: this.field.max,
        step: this.field.step,
        pattern: this.field.pattern,
        placeholder: this.field.placeholder || this.field.name,
        class: this.errorClasses,
      };
    },

    extraAttributes() {
      return {
        ...this.defaultAttributes,
        ...this.field.extraAttributes,
      };
    },
  },
};
</script>

import FormField from './components/FormField';
import DetailField from './components/DetailField';

Nova.booting((Vue, router) => {
  Vue.component('form-simple-repeatable', FormField);
  Vue.component('detail-simple-repeatable', DetailField);
});

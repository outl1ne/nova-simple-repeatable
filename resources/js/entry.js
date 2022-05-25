import DetailField from './components/DetailField';
import FormField from './components/FormField';

let simpleRepeatableDarkModeObserver = null;

Nova.booting((Vue, store) => {
  simpleRepeatableDarkModeObserver = new MutationObserver(() => {
    const cls = document.documentElement.classList;
    const isDarkMode = cls.contains('dark');

    if (isDarkMode && !cls.contains('nsr-dark')) {
      cls.add('nsr-dark');
    } else if (!isDarkMode && cls.contains('nsr-dark')) {
      cls.remove('nsr-dark');
    }
  }).observe(document.documentElement, {
    attributes: true,
    attributeOldValue: true,
    attributeFilter: ['class'],
  });

  Vue.component('detail-simple-repeatable', DetailField);
  Vue.component('form-simple-repeatable', FormField);
});

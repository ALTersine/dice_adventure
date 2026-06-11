import { registerVueControllerComponents } from '@symfony/ux-vue';
import './stimulus_bootstrap.js';
import './styles/app.css';

// Enregistre les composants Vue de assets/vue/controllers comme contrôleurs UX
registerVueControllerComponents(require.context('./vue/controllers', true, /\.vue$/));

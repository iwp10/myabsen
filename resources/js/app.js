import './bootstrap';

import Alpine from 'alpinejs';
import { pilihanMassal } from './pilihan-massal';

window.Alpine = Alpine;
window.pilihanMassal = pilihanMassal;

Alpine.data('pilihanMassal', pilihanMassal);

Alpine.start();

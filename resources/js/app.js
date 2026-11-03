
import Alpine from 'alpinejs';
import { pilihanMassal } from './pilihan-massal';
import { kelolaFilterKelas } from './filter-kelas-jadwal';

window.Alpine = Alpine;
window.pilihanMassal = pilihanMassal;
window.kelolaFilterKelas = kelolaFilterKelas;

Alpine.data('pilihanMassal', pilihanMassal);
Alpine.data('kelolaFilterKelas', kelolaFilterKelas);

Alpine.start();

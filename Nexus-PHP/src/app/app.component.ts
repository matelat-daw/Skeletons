import { Component, OnInit } from '@angular/core';
import { RouterOutlet } from '@angular/router';

declare var particlesJS: any;

@Component({
  selector: 'app-root',
  imports: [RouterOutlet],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent implements OnInit {
  title = 'nexus-astralis-front';
  
  ngOnInit() {
    this.initParticles();
  }
  
  async initParticles() {
    try {
      // Cargar configuración desde el archivo JSON
      const response = await fetch('/assets/particles.json');
      const particlesConfig = await response.json();
      
      // Inicializar particles.js con la configuración cargada
      particlesJS('particles-js', particlesConfig);
    } catch (error) {
      console.error('Error cargando configuración de partículas:', error);
      
      // Configuración de fallback en caso de error
    //   this.initParticlesFallback();
    }
  }
  
//   initParticlesFallback() {
//     // Configuración básica de fallback
//     particlesJS('particles-js', {
//       "particles": {
//         "number": {
//           "value": 100,
//           "density": {
//             "enable": true,
//             "value_area": 800
//           }
//         },
//         "color": {
//           "value": "#ffffff"
//         },
//         "shape": {
//           "type": "circle"
//         },
//         "opacity": {
//           "value": 0.5
//         },
//         "size": {
//           "value": 2,
//           "random": true
//         },
//         "move": {
//           "enable": true,
//           "speed": 0.1
//         }
//       },
//       "interactivity": {
//         "detect_on": "canvas",
//         "events": {
//           "onhover": {
//             "enable": true,
//             "mode": "bubble"
//           },
//           "onclick": {
//             "enable": true,
//             "mode": "push"
//           }
//         }
//       },
//       "retina_detect": true
//     });
//   }
}
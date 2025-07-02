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
    setTimeout(() => {
      this.initParticles();
    }, 100);
  }
  
  async initParticles() {
    try {
      const response = await fetch('/assets/particles.json');
      const particlesConfig = await response.json();
      particlesJS('particles-js', particlesConfig);
      console.log('Partículas inicializadas correctamente');
    } catch (error) {
      console.error('Error cargando configuración de partículas:', error);
    }
  }
}
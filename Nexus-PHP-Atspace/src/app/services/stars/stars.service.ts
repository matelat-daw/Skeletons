import { Injectable } from '@angular/core';
import { Star } from '../../models/star';

@Injectable({
  providedIn: 'root'
})
export class StarsService {

  private readonly API_URL = 'https://settled-muskrat-peaceful.ngrok-free.app/api/Stars'
  
  private readonly headers = {
    'Content-Type': 'application/json',
    'ngrok-skip-browser-warning': 'true'
  };

  constructor() { }

  async getAll(): Promise<Star[]> {
    const data = await fetch(this.API_URL, { headers: this.headers });
    if (!data.ok) throw new Error(`Error fetching stars: ${data.status}`);
    return data.json();
  }

  async getById(id: number): Promise<Star> {
    const data = await fetch(`${this.API_URL}/${id}`, { headers: this.headers });
    if (!data.ok) throw new Error(`Error fetching star ${id}: ${data.status}`);
    return data.json();
  }

}

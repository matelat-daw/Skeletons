import { Injectable, signal, inject } from '@angular/core';
import { User } from '../../models/user';
import { AuthService } from '../auth/auth.service';

@Injectable({
  providedIn: 'root'
})
export class UsersService {

  private readonly API_URL = 'http://localhost:8080/api/Account';
  private authService = inject(AuthService);

  constructor() { }

  async getAll(): Promise<User[]> {
    const token = this.authService.token();
    const headers: HeadersInit = {
      'Content-Type': 'application/json'
    };
    
    // Agregar token de autorización si existe
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }
    
    const data = await fetch(`${this.API_URL}/GetUsers`, {
      method: 'GET',
      headers,
      credentials: 'include'
    });
    if (!data.ok) throw new Error(`Error fetching users: ${data.status}`);
    return data.json();
  }

  async getInfoByNick(nick: string): Promise<User> {
    const token = this.authService.token();
    const headers: HeadersInit = {
      'Content-Type': 'application/json'
    };
    
    // Agregar token de autorización si existe
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }
    
    const data = await fetch(`${this.API_URL}/GetUserInfo/${nick}`, {
      method: 'GET',
      headers,
      credentials: 'include'
    });
    if (!data.ok) throw new Error(`Error fetching user info: ${data.status}`);
    return data.json();
  }

  async getMyProfile(): Promise<User> {
    const token = this.authService.token();
    const headers: HeadersInit = {
      'Content-Type': 'application/json'
    };
    
    // Agregar token de autorización si existe
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }
    
    const data = await fetch(`${this.API_URL}/Profile`, {
      method: 'GET',
      headers,
      credentials: 'include'
    });
    if (!data.ok) throw new Error(`Error fetching user profile: ${data.status}`);
    
    const response = await data.json();
    
    // La API devuelve {message: "...", data: {...}}, necesitamos solo los datos
    if (response.data) {
      return response.data;
    }
    
    // Si no hay estructura data, asumir que es el objeto directo (para compatibilidad)
    return response;
  }

  async addComment(comment: string, constellationId: number): Promise<boolean> {
    try {
      const commentData = {
        comment: comment,
        constellationId: constellationId,
      };
      
      const data = await fetch(`${this.API_URL}/Comments`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(commentData),
        credentials: 'include'
      });
      
      if (!data.ok) throw new Error(`Error adding comment: ${data.status}`);
      const response = await data.json();
      return response.success === true;
    } catch (error: any) {
      console.error('Error al añadir comentario:', error);
      throw error;
    }
  }

  async deleteComment(id: number): Promise<boolean> {
    try{
      const data = await fetch(`${this.API_URL}/Comments/${id}`, {
        method: 'DELETE',
        credentials: 'include'
      });
      
      if (!data.ok) {
        const errorText = await data.text();
        throw new Error(`Error deleting comment: ${errorText}`);
      }
      const response = await data.json();
      return response.success === true;
    } catch (error: any) {
      console.error('Error eliminando comentario:', error);
      throw error;
    }
  }

  async addFavorite(id: number): Promise<boolean> {
    const data = await fetch(`${this.API_URL}/Favorites/${id}`, {
      method: 'POST',
      credentials: 'include'
    });
    if (!data.ok) throw new Error(`Error adding favorite: ${data.status}`);
    const response = await data.json();
    return response.success === true;
  }

  async deleteFavorite(id: number): Promise<boolean> {
    const data = await fetch(`${this.API_URL}/Favorites/${id}`, {
      method: 'DELETE',
      credentials: 'include'
    });
    if (!data.ok) throw new Error(`Error deleting favorite: ${data.status}`);
    const response = await data.json();
    return response.success === true;
  }

  async isMyFavorite(id: number): Promise<boolean> {
    const data = await fetch(`${this.API_URL}/Favorites/${id}`, {
      method: 'GET',
      credentials: 'include'
    });
    if (!data.ok) throw new Error(`Error checking favorite: ${data.status}`);
    const response = await data.json();
    return response.success === true && response.data?.isFavorite === true;
  }

  async editProfile(profile: User): Promise<boolean> {
    if (!profile.name || !profile.nick || !profile.email || !profile.surname1) {
      throw new Error('Faltan campos obligatorios: nombre, nick, email o apellido');
    }
    const formData = new FormData();
    formData.append('Name', profile.name);
    formData.append('Nick', profile.nick);
    formData.append('Email', profile.email);
    formData.append('Surname1', profile.surname1);
    if (profile.surname2) formData.append('Surname2', profile.surname2);
    if (profile.phoneNumber) formData.append('PhoneNumber', profile.phoneNumber);  // Ya es string
    if (profile.userLocation) formData.append('UserLocation', profile.userLocation);
    if (profile.about) formData.append('About', profile.about);
    if (profile.bday) formData.append('Bday', new Date(profile.bday).toLocaleDateString());
    formData.append('PublicProfile', profile.publicProfile === true ? "1" : "0");
    
    try {
      const response = await fetch(`${this.API_URL}/Update`, {
        method: 'PATCH',
        body: formData,
        credentials: 'include'
      });
      
      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`${errorText}`);
      }
      const responseText = await response.text();
      return responseText === "Datos Actualizados.";
    } catch (error: any) {
      throw error;
    }
  }

  async deleteMyAccount(): Promise<void> {
    const response = await fetch(`${this.API_URL}/Delete`, {
      method: 'DELETE',
      credentials: 'include'
    });
    if (!response.ok) throw new Error(response.statusText);
    sessionStorage.clear();
    this.authService.token.set(null);
    window.location.reload();
  }
}
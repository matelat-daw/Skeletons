const express = require('express');
const cors = require('cors');
const app = express();

// CORS configuration
app.use(cors({
    origin: ['http://localhost:4200', 'https://b895-88-24-26-59.ngrok-free.app'],
    credentials: true,
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept', 'Origin', 'ngrok-skip-browser-warning']
}));

app.use(express.json());

// Test endpoint
app.get('/test', (req, res) => {
    res.json({
        success: true,
        message: 'Node.js test server working!',
        timestamp: new Date().toISOString()
    });
});

// Login endpoint
app.post('/api/Auth/Login', (req, res) => {
    console.log('Login attempt:', req.body);
    
    const { email, password } = req.body;
    
    if (!email || !password) {
        return res.status(400).json({ error: 'Email and password required' });
    }
    
    // Test credentials
    if (email === 'cesarmatelat@gmail.com' && password === 'test123') {
        const user = {
            id: 1,
            email: email,
            name: 'Test User',
            createdAt: new Date().toISOString(),
            isEmailConfirmed: true
        };
        
        res.json({
            success: true,
            message: 'Login successful',
            user: user
        });
        console.log('Login successful for:', email);
    } else {
        res.status(401).json({
            success: false,
            error: 'Invalid credentials'
        });
        console.log('Login failed for:', email);
    }
});

// Profile endpoint
app.get('/api/Account/Profile', (req, res) => {
    const user = {
        id: 1,
        email: 'cesarmatelat@gmail.com',
        name: 'Test User',
        createdAt: new Date().toISOString(),
        isEmailConfirmed: true
    };
    
    res.json({
        success: true,
        user: user
    });
});

// Logout endpoint
app.post('/api/Account/Logout', (req, res) => {
    res.json({
        success: true,
        message: 'Logout successful'
    });
});

// Constellations endpoint
app.get('/api/Constellations', (req, res) => {
    console.log('Constellations endpoint accessed');
    
    // Datos de constelaciones de muestra
    const constellations = [
        {
            id: 1,
            code: 'AND',
            latin_name: 'Andromeda',
            english_name: 'Andromeda',
            spanish_name: 'Andrómeda',
            mythology: 'Hija de los reyes de Etiopía, Cefeo y Casiopea',
            area_degrees: 722.278,
            declination: '+40°',
            celestial_zone: 'Northern',
            ecliptic_zone: 'Near ecliptic',
            brightest_star: 'Alpheratz',
            discovery: 'Ancient',
            image_name: 'andromeda.jpg',
            image_url: null
        },
        {
            id: 2,
            code: 'CAS',
            latin_name: 'Cassiopeia',
            english_name: 'Cassiopeia',
            spanish_name: 'Casiopea',
            mythology: 'Reina de Etiopía, madre de Andrómeda',
            area_degrees: 598.407,
            declination: '+60°',
            celestial_zone: 'Northern',
            ecliptic_zone: 'Far from ecliptic',
            brightest_star: 'Schedar',
            discovery: 'Ancient',
            image_name: 'cassiopeia.jpg',
            image_url: null
        },
        {
            id: 3,
            code: 'ORI',
            latin_name: 'Orion',
            english_name: 'Orion',
            spanish_name: 'Orión',
            mythology: 'El cazador gigante de la mitología griega',
            area_degrees: 594.120,
            declination: '+05°',
            celestial_zone: 'Equatorial',
            ecliptic_zone: 'Near ecliptic',
            brightest_star: 'Rigel',
            discovery: 'Ancient',
            image_name: 'orion.jpg',
            image_url: null
        },
        {
            id: 4,
            code: 'UMA',
            latin_name: 'Ursa Major',
            english_name: 'Ursa Major',
            spanish_name: 'Osa Mayor',
            mythology: 'La Gran Osa, una de las constelaciones más reconocibles',
            area_degrees: 1279.660,
            declination: '+55°',
            celestial_zone: 'Northern',
            ecliptic_zone: 'Far from ecliptic',
            brightest_star: 'Alioth',
            discovery: 'Ancient',
            image_name: 'ursa_major.jpg',
            image_url: null
        },
        {
            id: 5,
            code: 'LEO',
            latin_name: 'Leo',
            english_name: 'Leo',
            spanish_name: 'León',
            mythology: 'El león de Nemea, vencido por Hércules',
            area_degrees: 946.964,
            declination: '+15°',
            celestial_zone: 'Northern',
            ecliptic_zone: 'On ecliptic',
            brightest_star: 'Regulus',
            discovery: 'Ancient',
            image_name: 'leo.jpg',
            image_url: null
        }
    ];
    
    res.json(constellations);
    console.log(`Returned ${constellations.length} constellations`);
});

const PORT = 8000;
app.listen(PORT, () => {
    console.log(`Server running on http://localhost:${PORT}`);
});

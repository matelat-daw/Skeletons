const express = require('express');
const cors = require('cors');
const app = express();

// CORS configuration
app.use(cors({
    origin: ['http://localhost:4200', 'https://d1de-88-24-26-59.ngrok-free.app'],
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

const PORT = 8000;
app.listen(PORT, () => {
    console.log(`Server running on http://localhost:${PORT}`);
});

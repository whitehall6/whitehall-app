<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>WHITE HALL - Bienvenue</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: Calibri, sans-serif;
      background: linear-gradient(to bottom, #ffffff, #f0f4f8);
      color: #1a1a1a;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: space-between;
      padding: 40px 20px;
    }
    .top-bar {
      position: absolute;
      top: 20px;
      right: 20px;
    }
    .btn-login {
      background-color: #003366;
      color: #fff;
      padding: 10px 20px;
      border-radius: 25px;
      text-decoration: none;
      font-size: 14px;
      transition: background 0.3s;
    }
    .btn-login:hover {
      background-color: #002244;
    }
    .logo {
      margin-top: 40px;
    }
    .logo img {
      width: 130px;
    }
    .content {
      text-align: center;
      max-width: 400px;
      margin: 30px auto;
    }
    .content h1 {
      font-size: 24px;
      margin-bottom: 15px;
      color: #001F3F;
    }
    .content p {
      font-size: 16px;
      color: #444;
    }
    .btn-register {
      background-color: #D4AF37;
      color: #fff;
      padding: 14px 28px;
      border-radius: 30px;
      text-decoration: none;
      font-size: 16px;
      transition: background 0.3s;
    }
    .btn-register:hover {
      background-color: #c39c1e;
    }
  </style>
</head>
<body>

  <div class="top-bar">
    <a href="login.php" class="btn-login">Se connecter</a>
  </div>

  <div class="logo">
   <img src="http://localhost/White-Hall/images/logos.jpeg" alt="Logo White Hall" class="logo">
  </div>

  <div class="content">
    <h1>Bienvenue sur WHITE HALL</h1>
    <p>
      Accédez à des formations pratiques, gagnez des commissions avec notre système de vendeur.  
      <br><br>
      Inscrivez-vous ou connectez-vous pour commencer votre expérience.
    </p>
  </div>

  <a href="register.php" class="btn-register">S’inscrire</a>

</body>
</html>
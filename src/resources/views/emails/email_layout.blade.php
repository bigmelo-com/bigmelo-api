<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap');
        
        .container{
            width: 100%;
            height: 100%;
            margin: 0 auto;
            text-align: center;
            font-family: 'Space Grotesk', sans-serif;
            background: #20262E;
            color: #FFF;
            border-radius: 20px;
        }

        .content{
            
        }

        .title{
            background: #34D3A3;
            padding: 2rem;
        }

        .description{
            padding: 1rem;
            font-size: 1rem;
        }

        .button{
            background: #34D3A3;
            color: #FFF;
            padding: 1rem;
            font-size: 1rem;
            border-radius: 20px;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="title">Bigmelo</h1>
        <div class="content">
            @yield('content')
        </div>
    </div>
</body>
</html>

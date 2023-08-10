
<!DOCTYPE html>
<html>
<head>
    <title>Stripe Account Onboarding</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .text2 {
         margin-top: 5% !important;
    }

    .justify-content-center img{
        padding-top: 80px;
    }

    .text-last{

        padding-top: 85px;

    }
    body{
        font-family: Poppins;
    }
    .text{
        color: white;
    }
    .header-text
    {
        margin-top: 11% !important;
        font-weight: 800;
        color: white;
    }
    .butns
    {
        background-color: #FFCC64;
        height: 60px;
        width: 360px;
        font-size: 18px;
        /* font: bolder; */
        font-weight: bolder;
        /* font-weight: 1000 !important; */
        border-radius: 18px;
        text-decoration: none;
        color: rgb(25, 24, 24);
        padding-top: 16px;
    }
    .text{
        text-align: center;
    }
    a:hover{
        color: white;
    }
    
    </style>
</head>
<body style="background-color: #1e529d">
     <div class="container mt-3" style="">
            <div class="row">
                <div class="col-md-12">
                   
                        <div class="d-flex justify-content-center">
                            <h3 class="header-text">Welcome to Stripe <br> Account Onboarding!</h3>
                         </div>

                         <div class="d-flex justify-content-center">
                            <img src="{{ asset('assets/payment.png') }}" alt="">
                         </div>
                         </div>
                         <div class="card-block text2 d-flex justify-content-center text-last">
                            <p class="text">Please click the button below to complete the account onboarding process</p>
                        </div>
                        <div class="card-block text2 d-flex justify-content-center" style="position: fixed; bottom: 25px;">
                            <a  href="{{ $accountLink->url }}" class="butns  ml-5 d-flex justify-content-center" >Complete Onboarding</a>
                        </div>
                      </div>
                </div>
           
        </div>
</body>
</html>

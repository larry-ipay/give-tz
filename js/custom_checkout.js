
// jQuery(document).ready(function($){

//     function myCheckout() {  
//         try {
//             // console.log("data received", paymentData);
//             let total = paymentData.total;
//             let email = paymentData.email;
//             let oid = paymentData.oid;
//             let curr = paymentData.curr;
//             let vid = paymentData.vid;  
//             let elipa = new ELIPA({ MERCHANT_ID: vid });
//             let data = {
//                 amount: total,
//                 email: email,
//                 order_id: oid,
//                 currency: curr,
//                 channel: ['tigo','airtelmoney'],
//                 theme: "black",
//                 logo: "https://static-00.iconduck.com/assets.00/linkedin-icon-2048x2048-ya5g47j2.png",
//                 callback: function (response) {
//                     console.log("callback returned", response);
//                     // Handle callback response here
//                     if(response && response.response_code === 200){
//                         // Make API request to provided URL and if status 200 post to callback handler
//                         let sendRes = {
//                             action:'tz_callback',
//                             data: response,
//                             nonce: paymentData.nonce,
//                             order_id: oid
//                         }

//                         $.post(paymentData.ajax_url, sendRes, function(res) {
//                         // Handle the response from the server here
//                             // console.log(res);
//                             var jsonParse = JSON.parse(res);

//                         // check if order_id is defined in the response
//                         if(jsonParse.success){
//                             // console.log(jsonParse.message);
//                             alert("Kindly wait for the page to redirect after you press OK on this alert");
//                             window.location.href = jsonParse.url;
//                             // var thankYouUrl = paymentData.site_url + '/checkout/order-received/'+sendRes.order_id+'/';
                           
//                             // window.location.href = thankYouUrl;
//                         }else{
                            
//                             alert(jsonParse.message);
//                             // window.location.href = paymentData.site_url + '/checkout/?payment_failed=true';
//                             window.location.href = jsonParse.url;
//                         }

//                         });
                
//                     }else{
//                         // Failed with error
           
//                         alert(response.response_text);
//                           // Handle callback response here
//                           window.location.href = paymentData.site_url;
//                     }
                
//                 },
//                 country: "TZ"
//             };

//             elipa.start(data);
            
//         } catch (err) {
//             console.log("error", err);
            
//         }
    
//     }

//     myCheckout();

// });


jQuery(document).ready(function($){

    console.log("document loaded");

    $('#give-purchase-button').closest('.give-form').submit(function(e){
        e.preventDefault();
        console.log("Donate now button clicked");
    });

    $('.give-btn').closest('.give-form').submit(function(e){
        e.preventDefault();
        console.log("Donate now button clicked");
    });
    
    $('.give-form').submit(function(e){
        e.preventDefault();
        console.log("form is submitted");
    });
});
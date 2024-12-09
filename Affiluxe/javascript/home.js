document.addEventListener('DOMContentLoaded', function () {
    const menuButton = document.getElementById('menu');
    const menuList = document.getElementById('menu-con');

    menuButton.addEventListener('click', function() {
        // Toggle the "hidden" class on the menu list
        menuList.classList.toggle('hidden');
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const menuButton = document.getElementById('img');
    const menuList = document.getElementById('img');

    menuButton.addEventListener('click', function() {
        // Toggle the "hidden" class on the menu list
        menuList.classList.toggle('size');
    });
});



 



   

        $("#daily").click(function(){
            $("#dad").css("display", "contents");
        });


        $("#notifications").click(function(){
            $("#note").css("display", "contents");
        });

 
        $("#withdraw").click(function(){
            $("#wy").css("display", "contents");
        });

        
 
        $("#upgrade").click(function(){
            $("#up").css("display", "contents");
        });

 
        
        $("#sponsor").click(function(){
            $("#sspp").css("display", "contents");
        });
        
 
        
        $("#spin").click(function(){
            $("#spin-up").css("display", "contents");
        });


         




        $("#invest").click(function(){
            $("#pay").css("display", "contents");
        });





//dark mode
        $("#mode").click(function(){
            $("#body").css("background", "linear-gradient(darkblue, blue, black,grey)");
            $("#menu-con").css("background", "linear-gradient( currentcolor, darkblue, blue, black,grey)");
            $("#notify").css("background", "linear-gradient( currentcolor, darkblue, blue, black, grey)");
            $("#sponsorer").css("background", "linear-gradient( currentcolor, darkblue, blue, black,grey)");
            $("#dailly").css("background", "linear-gradient( currentcolor, darkblue, blue, black, grey)");
            $("#upgrades").css("background", "linear-gradient( currentcolor, darkblue, blue, black,grey)");
            $("#withdrawal").css("background", "linear-gradient( currentcolor, darkblue, blue, black,grey)");
            $("#draw").css("background", "linear-gradient(currentcolor, darkblue, blue, black,grey)");
             
            $("#container").css("background", "linear-gradient(currentcolor, darkblue, blue, black)");
            $("#message").css("color", "black");
        });
//

document.getElementById("menu").onclick=function() {
    document.getElementById("menu-con").style.background = "repeating-linear-gradient(11deg, white, black 611px)";
    
    
};



document.getElementById("notifications").onclick=function() {
    document.getElementById("notify").style.height="120%";
};

 

 



document.getElementById("invest").onclick=function() {
    document.getElementById("invester").style.height="120%";
};


document.getElementById("spin").onclick=function() {
    document.getElementById("spin-to").style.height="120%";
    alert("This Feature Is Not Yet Ready!") ;
     
};


document.getElementById("sponsor").onclick=function() {
    document.getElementById("sponsorer").style.height="120%";
};



document.getElementById("daily").onclick=function() {
    document.getElementById("dailly").style.height = "120%";
 
};


 
document.getElementById("game").onclick= function(){
            alert("this feature is not available yet come back next time!")
        }

document.getElementById("withdraw").onclick = function () {
alert("withdrawal can not be made until futher notice if you wish to continue click OK")
    document.getElementById("withdrawal").style.height="120%";
};



document.getElementById("upgrade").onclick=function() {
    document.getElementById("upgrades").style.height="120%";
};

 

 
document.getElementById("channels").onclick = function () {
     alert("This feature is not available at the moment updates are been made!")
 }

function copyText() {
    /* Get the text field */
    var copyText = document.getElementById("myText");

    /* Select the text field */
    copyText.select();
    copyText.setSelectionRange(0, 99999); // For mobile devices

    /* Copy the text inside the text field */
    document.execCommand("copy");

    /* Alert the user that the text was copied */
}
    $("#copy").click(function(){
        $("#message-1").css("color", "rgb(3, 93, 3)");
      
           preventDefault.reset
    });

 

    function Text() {
    /* Get the text field */
    var copyText = document.getElementById("Text");

    /* Select the text field */
    copyText.select();
    copyText.setSelectionRange(0, 99999); // For mobile devices

    /* Copy the text inside the text field */
    document.execCommand("copy");

    /* Alert the user that the text was copied */
}
    $("#copy").click(function(){
        $("#message-1").css("color", "rgb(3, 93, 3)");
      
           preventDefault.reset
    });





// Simulated user balance
let userBalance = document.getElementById("availableBalance").value; // Example balance

// Function to process the withdrawal
function processWithdrawal() {
    const bankName = document.getElementById('bankName').value;
    const accountName = document.getElementById('accountName').value;
    const accountNumber = document.getElementById('accountNumber').value;
    const withdrawalAmount = (document.getElementById('withdrawalAmount').value);

    // Validate the withdrawal form
    if (!bankName || !accountName || !accountNumber || isNaN(withdrawalAmount) || withdrawalAmount <= 0) {
        document.getElementById('withdrawalError').innerText = 'Please fill in all fields correctly!';
        return;
    }
        // Check if account number is valid (at least 10 digits)
        if (accountNumber.length < 10) {
            alert("Invalid account number!");
            return;
    }
    

    // Check if the withdrawal amount is valid (i.e., user has enough balance)
    if (withdrawalAmount > userBalance) {
        document.getElementById('withdrawalError').innerText = 'Insufficient balance!';
        return;
    } 

      // Check if the withdrawal amount is valid (i.e., user has enough balance)
      if (withdrawalAmount > 100) {
        document.getElementById('withdrawalError').innerText = 'maximum withdrawal $100!';
        return;
    } 
    if (withdrawalAmount < 100) {
        document.getElementById('withdrawalError').innerText = 'minimum withdrawal $100!';
        return;
    } 

    currentUserData.userBalance -= withdrawAmount;
    userBalance -= withdrawAmount;


}



    const currentUser = localStorage.getItem('currentUser');
        const userData = JSON.parse(localStorage.getItem('users')) || [];
        const currentUserData = userData.find(user => user.username === currentUser);

        document.getElementById('dashboardUsername').textContent = currentUser;
        document.getElementById('balanceAmount').textContent = currentUserData.taskBalance;
        document.getElementById('referralBalance').textContent = currentUserData.referralBalance;
        document.getElementById('totalBalance').textContent = currentUserData.taskBalance + currentUserData.referralBalance;

        // Update available balance to $100 if referral balance is >= 100
        let availableBalance = 0.00;
        if (currentUserData.taskBalance >=100) {
            availableBalance = 100;
        }
        document.getElementById('availableBalance').textContent = availableBalance;

        let taskButton = document.getElementById('addFundsButton');
        let taskBalanceElement = document.getElementById('balanceAmount');
        let totalBalanceElement = document.getElementById('totalBalance');
        let availableBalanceElement = document.getElementById('availableBalance');

        let lastTaskTime = localStorage.getItem(`${currentUser}-lastTaskTime`);

        if (lastTaskTime && new Date().getTime() - lastTaskTime <   24*60*60* 1000) {
            taskButton.disabled = true;
        }

        taskButton.addEventListener('click', function() {
            currentUserData.taskBalance += 1.00;
            localStorage.setItem('users', JSON.stringify(userData));
            taskBalanceElement.textContent = `$${currentUserData.taskBalance}`;
            totalBalanceElement.textContent = `$${currentUserData.taskBalance + currentUserData.referralBalance}`;
            localStorage.setItem(`${currentUser}-lastTaskTime`, new Date().getTime());
            taskButton.disabled = true;
        });





 

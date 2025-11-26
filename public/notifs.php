<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
  window.OneSignalDeferred = window.OneSignalDeferred || [];
  OneSignalDeferred.push(async function(OneSignal) {
    await OneSignal.init({
      appId: "48497eb5-e6d4-40e0-95df-f8cea3dd4de1",
    });

    const subId = await OneSignal.User.PushSubscription.id;
    const isSubscribed = await OneSignal.User.PushSubscription.optedIn;

    console.log("Push Subscription ID:", subId);
    console.log("Subscribes:", isSubscribed);

    fetch("saveSubscription.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        subId: subId,
        isSubscribed: isSubscribed,
      })
    })
    .then(response => response.text())
    .then(data => {
      console.log("Answer:", data);
    })
    .catch(error => {
      console.error("Error while sending:", error);
    });
  });
</script>

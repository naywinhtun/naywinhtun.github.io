<?php
echo "hello";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebRTC Test</title>
</head>
<body>
    <h2>WebRTC Test</h2>
    <video id="localVideo" autoplay playsinline></video>
    <video id="remoteVideo" autoplay playsinline></video>
    <button id="startCall">Start Call</button>

    <script>
        let localStream;
        let peerConnection;
        const localVideo = document.getElementById("localVideo");
        const remoteVideo = document.getElementById("remoteVideo");
        const startCallButton = document.getElementById("startCall");

        const config = { iceServers: [{ urls: "stun:stun.l.google.com:19302" }] };

        async function startWebRTC() {
            localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            localVideo.srcObject = localStream;

            peerConnection = new RTCPeerConnection(config);
            localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

            peerConnection.onicecandidate = event => {
                if (event.candidate) {
                    sendSignalingData({ candidate: event.candidate });
                }
            };

            peerConnection.ontrack = event => {
                remoteVideo.srcObject = event.streams[0];
            };

            const offer = await peerConnection.createOffer();
            await peerConnection.setLocalDescription(offer);
            sendSignalingData({ offer });
        }

        async function handleSignalingData(data) {
            if (data.offer) {
                await peerConnection.setRemoteDescription(new RTCSessionDescription(data.offer));
                const answer = await peerConnection.createAnswer();
                await peerConnection.setLocalDescription(answer);
                sendSignalingData({ answer });
            } else if (data.answer) {
                await peerConnection.setRemoteDescription(new RTCSessionDescription(data.answer));
            } else if (data.candidate) {
                await peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
            }
        }

        function sendSignalingData(data) {
            fetch("signaling.php", {
                method: "POST",
                body: JSON.stringify(data),
                headers: { "Content-Type": "application/json" }
            });
        }

        startCallButton.onclick = startWebRTC;
    </script>
</body>
</html>

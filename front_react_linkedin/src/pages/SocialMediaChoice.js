import React from "react";
import "./css/SocialMediaChoice.css";

const SocialMediaChoice = () => {
    const handleChoice = (platform) => {
        if (platform === "LinkedIn") {
          window.location.href = "http://localhost:3000/login";
        } else if (platform === "Facebook") {
          window.location.href = "http://localhost:8000/facebook/redirect";
        }
      };
      return (
        <div className="social-media-container">
          <div className="logo facebook-logo" onClick={() => handleChoice("Facebook")}>
            <img
              src="https://upload.wikimedia.org/wikipedia/commons/5/51/Facebook_f_logo_%282019%29.svg"
              alt="Facebook"
            />
          </div>
          <div className="logo linkedin-logo" onClick={() => handleChoice("LinkedIn")}>
            <img
              src="https://upload.wikimedia.org/wikipedia/commons/c/ca/LinkedIn_logo_initials.png"
              alt="LinkedIn"
            />
          </div>
        </div>
      );
    };

export default SocialMediaChoice;

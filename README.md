# Ipad-clock-message
A small group of html, php and text file to display clock and or messages on old iPads using synology web server
- This was developed as extension of manually created simple clock display page with use of google ai.
- The purpose was to provide an aid to a family for a person with mild dementia.
- For our use we leave messages for our elderly parent when we leave them in the house, such that they know where we are.

# Design
Uses existing in house facilities. The domestic synology nas with web server and php (8.2). Some superseded iPad's.
The iPad displays a day, date and time in safari in full screen. The html polls the server checking for a non empty message txt file. If found with a message the display then cycles at 10 second intervals between the message+time and the day/date/time display.

The complementary part is manage.php on the server which provides the means to set up the message as needed.  As some messages are commonly used. It has three presets to store those messages. The message is stored as plain text in messages.txt, the presets are stored in presets.ini.

# Commentary 
The message display side has been targeted at an iPad 1 running iOS 5.  The management target at current (ios26 class) devices.
The server side is basic the only issues encountered was that of ensuring that the web server process had write access to message.txt and presets.ini.

The AI was very useful at the UI layout and the fiddly and boring script work. Also useful at adapting contemporary HTML down to levels suitable for the iPad 1 whilst maintaining visual appearance. Not so good when the result of the code generator was mangled by the rendering system (throwing in backslashes).  Eventually resolved by prompting for a zip file instead of text displays.  That prompt also meant that it circumvented the common problem of the renderer breaking the markdown display by overfilling the text boxes.

# Not done

- It works purely in house or via vpn. No remote access is available.  Looked at a couple of options. But not pursued unless it is found useful.
- No multiple message handling.
- Fully open to race conditions if multiple users access it.

# Availability 
This code is freely available for any usage by anyone.
It is fairly straightforward and should be adaptable to other contexts (Android) / servers.
That, as the saying goes,  is left as an exercise for the student.

Manager view on iPad safari.


<img width="640" height="445" alt="manager view" src="https://github.com/user-attachments/assets/abffcbe5-e7e7-4182-9205-49ba19b7b525" />

Clock without message 


<img width="320" height="223" alt="image" src="https://github.com/user-attachments/assets/b2f077bb-41d2-41bc-aaab-bcbae700fe96" />


Clock with message

<img width="320" height="223" alt="Message" src="https://github.com/user-attachments/assets/9ec6d4f4-470a-48b9-bd05-e41f3847532a" 
/>



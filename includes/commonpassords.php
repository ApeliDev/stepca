<?php
/**
 * Common passwords list for password validation
 * This file contains an array of commonly used passwords that should be rejected
 */

return [
    // Basic weak passwords
    'password', 'admin123', '123456789', 'qwerty123', 'admin@123', 'password123', 
    'administrator', 'welcome123', 'stepcashier', '123456', '12345678', '12345', 
    '1234567', 'qwerty', 'abc123', '111111', '123123', 'letmein', 'monkey', 
    'dragon', 'baseball', 'iloveyou', 'trustno1', 'sunshine', 'master', 'hello', 
    'freedom', 'whatever', 'qazwsx', '654321', 'superman', '1qaz2wsx', 'password1', 
    'welcome', 'login', 'admin', 'passw0rd', 'starwars', 'football',

    // Names with numbers
    'michael', 'shadow', 'hannah', 'jessica', 'ashley', 'bailey', 'charlie', 
    'daniel', 'matthew', 'andrew', 'michelle', 'tigger', 'princess', 'joshua', 
    'jessica1', 'jordan1', 'jennifer1', 'hunter1', 'fuckyou1', 'thomas1', 
    'robert1', 'access123', 'love123', 'buster1', 'soccer1', 'hockey1', 
    'killer1', 'george1', 'sexy123', 'andrew1', 'charlie1',

    // Names without numbers
    'jordan', 'jennifer', 'hunter', 'fuckyou', 'thomas', 'robert', 'access', 
    'love', 'buster', 'soccer', 'hockey', 'killer', 'george', 'sexy', 'andrew', 
    'asshole', 'dallas', 'panties', 'pepper', 'ginger', 'hammer', 'summer', 
    'corvette', 'taylor', 'fucker', 'austin',

    // Simple patterns
    '1234', 'a1b2c3', 'definition', '123qwe', 'zaq12wsx', 'qwertyuiop', 
    'zxcvbnm', 'asdfgh', 'poiuyt', 'lkjhgf', 'mnbvcx', '987654321', 
    '1111111', '0000000', '000000', '1234567890',

    // Password variations
    'password!', 'Password', 'PASSWORD', 'passw0rd',

    // System/Tech terms
    'Admin', 'ADMIN', 'root', 'user', 'guest', 'demo', 'test123', 'temp', 
    'default', 'public', 'private', 'secret', 'secure', 'super', 'system', 
    'windows', 'linux', 'ubuntu', 'oracle', 'mysql', 'postgres', 'database', 
    'server', 'computer', 'laptop', 'desktop', 'iphone', 'android',

    // Brands/Companies
    'google', 'facebook', 'twitter', 'instagram', 'youtube', 'amazon', 'apple', 
    'microsoft', 'adobe', 'netflix', 'spotify', 'paypal', 'ebay', 'walmart', 
    'target', 'costco', 'bestbuy', 'homedepot', 'lowes', 'mcdonalds', 
    'starbucks', 'subway',

    // Food/Drinks
    'pizza', 'burger', 'chicken', 'coffee', 'beer', 'wine', 'vodka', 'whiskey',

    // Money/Finance
    'money', 'dollar', 'bitcoin', 'crypto', 'gold', 'silver', 'diamond', 
    'ruby', 'emerald', 'sapphire', 'pearl', 'crystal',

    // Fantasy/Mythical
    'rainbow', 'unicorn', 'phoenix',

    // Animals
    'eagle', 'lion', 'tiger', 'bear', 'wolf', 'shark', 'dolphin', 'whale', 
    'elephant', 'giraffe', 'zebra', 'hippo', 'rhino', 'kangaroo', 'penguin', 
    'flamingo', 'peacock', 'butterfly', 'spider', 'snake', 'turtle', 'frog', 
    'fish', 'bird', 'cat', 'dog', 'horse', 'cow', 'pig', 'sheep', 'goat', 
    'rabbit', 'mouse', 'rat',

    // Flowers/Fruits
    'flower', 'rose', 'lily', 'daisy', 'tulip', 'sunflower', 'cherry', 'apple', 
    'banana', 'orange', 'grape', 'strawberry', 'blueberry', 'raspberry', 
    'blackberry', 'pineapple', 'mango', 'peach', 'pear', 'plum', 'kiwi', 
    'coconut', 'lemon', 'lime',

    // Colors
    'red', 'blue', 'green', 'yellow', 'orange', 'purple', 'pink', 'black', 
    'white', 'gray', 'brown', 'silver',

    // Days/Months/Seasons
    'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 
    'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 
    'september', 'october', 'november', 'december', 'spring', 'summer', 
    'autumn', 'winter', 'morning', 'afternoon', 'evening', 'night', 'midnight', 
    'noon',

    // Places/Buildings
    'home', 'house', 'apartment', 'office', 'school', 'college', 'university', 
    'hospital', 'church', 'store', 'mall', 'park', 'beach', 'mountain', 'forest', 
    'desert', 'ocean', 'lake', 'river', 'bridge', 'road', 'street', 'avenue', 
    'boulevard',

    // Countries
    'america', 'canada', 'mexico', 'england', 'france', 'germany', 'italy', 
    'spain', 'russia', 'china', 'japan', 'india', 'australia', 'brazil', 
    'argentina', 'egypt', 'africa',

    // US Cities
    'newyork', 'losangeles', 'chicago', 'houston', 'phoenix', 'philadelphia', 
    'sanantonio', 'sandiego', 'dallas', 'sanjose', 'austin', 'jacksonville', 
    'fortworth', 'columbus', 'charlotte', 'seattle', 'denver', 'boston', 
    'detroit', 'nashville', 'portland', 'oklahoma', 'lasvegas', 'louisville', 
    'milwaukee', 'albuquerque', 'tucson', 'fresno', 'sacramento', 'mesa', 
    'kansas', 'atlanta', 'omaha', 'colorado', 'raleigh', 'virginia', 'miami', 
    'oakland', 'minneapolis', 'tulsa', 'cleveland', 'wichita', 'orleans', 
    'tampa', 'honolulu',

    // Keyboard patterns
    'qwertyui', 'asdfghjk', 'zxcvbnm123', 'q1w2e3r4', 'a1s2d3f4', 'z1x2c3v4', 
    '1q2w3e4r', '1a2s3d4f', '1z2x3c4v', 'qwer1234', 'asdf1234', 'zxcv1234', 
    '4321rewq', '4321fdsa', '4321vcxz', 'qazwsxedc', 'rfvtgbyhn', 'plokijuh',

    // Additional common phrases
    'iloveyou123', 'letmein123', 'trustno123', 'password!@#', 'welcome!@#',
    'admin!@#', '123456!@#', 'qwerty!@#', 'abc123!@#', 'letmein!@#',

    // More common/weak passwords
    'changeme', 'temp123', 'mypassword', 'newpassword', 'testtest',
    'pass1234', 'user1234', 'default123', 'guest123', 'root123',
    'adminadmin', 'qwertyui', 'asdfasdf', 'zxcvzxcv', 'password321',
    'welcome1', 'welcome12', 'welcome1234', 'password12', 'password1234'
];
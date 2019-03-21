# Hanzi Converter

This is super rough and I don't necessarily consider it ready for prime time but it does function.

This inserts a meta box into posts and pages.

The meta box accepts either a string of Hanzi or a some Chinese to English dialog.

If it gets a string of Hanzi it'll just return the Pinyin conversion of that text:

![Hanzi Converter With Hanzi Input](https://methnen-dropshare.s3.amazonaws.com/pb-JrAFQrmUL3.png)

For dialog it expects text in the format of English then Chinese or Chinese and then English:

```
那他自己能同意吗?
Would he agree with this?
你知道吗?
You know?
```

When it gets that kind of input it'll return something like this:

```
那他自己能同意吗?|nà tā zì jǐ néng tóng yì ma?|Would he agree with this?
你知道吗?|nǐ zhī dào ma?|You know?

```

![Hanzi Converter With Dialog Input](https://methnen-dropshare.s3.amazonaws.com/pb-JrAFQrmUL3.png)